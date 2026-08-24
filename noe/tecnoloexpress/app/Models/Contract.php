<?php

namespace App\Models;

use App\Enums\CommissionRole;
use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Enums\LeadStage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'number', 'type', 'property_id', 'lead_id', 'client_contact_id', 'owner_contact_id',
    'agent_id', 'amount', 'deposit', 'monthly_rent', 'currency', 'start_date', 'end_date',
    'signed_at', 'status', 'commission_percent', 'commission_amount', 'notes',
])]
class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'currency' => 'USD',
        'commission_percent' => 3,
    ];

    protected function casts(): array
    {
        return [
            'type' => ContractType::class,
            'status' => ContractStatus::class,
            'amount' => 'decimal:2',
            'deposit' => 'decimal:2',
            'monthly_rent' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'signed_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Contract $contract) {
            $contract->number ??= static::nextNumber();
            $contract->commission_amount = $contract->calculateCommission();
        });

        static::updating(function (Contract $contract) {
            if ($contract->isDirty(['amount', 'commission_percent'])) {
                $contract->commission_amount = $contract->calculateCommission();
            }
        });

        // Al activarse el contrato la propiedad cambia de estado, el lead se
        // marca como ganado y se generan las comisiones del agente.
        static::saved(function (Contract $contract) {
            if (! $contract->wasChanged('status') && ! $contract->wasRecentlyCreated) {
                return;
            }

            if ($contract->status->locksProperty()) {
                $contract->syncPropertyStatus();
                $contract->markLeadWon();
                $contract->generateCommissions();
            }
        });
    }

    /** Numeración correlativa por año: CTR-2026-0001. */
    public static function nextNumber(): string
    {
        $year = now()->year;
        $count = static::withTrashed()->whereYear('created_at', $year)->count();

        return sprintf('CTR-%d-%04d', $year, $count + 1);
    }

    /**
     * Base de cálculo: el importe de la operación en venta y reserva, y la
     * renta anual en alquiler (convención habitual del sector).
     */
    public function commissionBase(): float
    {
        return $this->type === ContractType::Alquiler
            ? (float) $this->monthly_rent * 12
            : (float) $this->amount;
    }

    public function calculateCommission(): float
    {
        return round($this->commissionBase() * ((float) $this->commission_percent / 100), 2);
    }

    public function syncPropertyStatus(): void
    {
        $this->property?->update(['status' => $this->type->resultingPropertyStatus()]);
    }

    public function markLeadWon(): void
    {
        if ($this->lead && $this->lead->stage !== LeadStage::Ganado) {
            $this->lead->update([
                'stage' => LeadStage::Ganado,
                'probability' => 100,
            ]);
        }
    }

    /**
     * Reparte la comisión entre el captador del inmueble y el agente que cierra.
     * Si son la misma persona recibe el total en una sola línea.
     */
    public function generateCommissions(): void
    {
        if ($this->commissions()->exists()) {
            return;
        }

        $closer = $this->agent_id;
        $capturer = $this->property?->agent_id;
        $total = (float) $this->commission_amount;

        if (! $closer && ! $capturer) {
            return;
        }

        if ($closer && $capturer && $closer !== $capturer) {
            $this->commissions()->createMany([
                [
                    'user_id' => $capturer,
                    'role' => CommissionRole::Captador,
                    'percent' => 50,
                    'amount' => round($total * 0.5, 2),
                ],
                [
                    'user_id' => $closer,
                    'role' => CommissionRole::Vendedor,
                    'percent' => 50,
                    'amount' => round($total * 0.5, 2),
                ],
            ]);

            return;
        }

        $this->commissions()->create([
            'user_id' => $closer ?? $capturer,
            'role' => CommissionRole::Vendedor,
            'percent' => 100,
            'amount' => $total,
        ]);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'client_contact_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'owner_contact_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }
}
