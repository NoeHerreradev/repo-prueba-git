<?php

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title', 'contact_id', 'property_id', 'assigned_agent_id', 'source', 'stage',
    'expected_value', 'probability', 'expected_close_date', 'lost_reason',
    'stage_changed_at', 'notes',
])]
class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'source' => LeadSource::class,
            'stage' => LeadStage::class,
            'expected_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'stage_changed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lead $lead) {
            $lead->stage_changed_at ??= now();
        });

        static::created(function (Lead $lead) {
            $lead->stageHistories()->create([
                'from_stage' => null,
                'to_stage' => $lead->stage->value,
                'user_id' => auth()->id(),
                'changed_at' => $lead->stage_changed_at ?? now(),
            ]);
        });

        // Registra cada cambio de etapa para poder medir la conversión del embudo.
        static::updating(function (Lead $lead) {
            if (! $lead->isDirty('stage')) {
                return;
            }

            $lead->stage_changed_at = now();

            $lead->stageHistories()->create([
                'from_stage' => $lead->getOriginal('stage'),
                'to_stage' => $lead->stage->value,
                'user_id' => auth()->id(),
                'changed_at' => now(),
            ]);
        });
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(LeadStageHistory::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subjectable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'subjectable');
    }

    /** Valor ponderado por la probabilidad de cierre. */
    public function weightedValue(): float
    {
        return (float) $this->expected_value * ($this->probability / 100);
    }

    /** Días transcurridos desde el último cambio de etapa. */
    public function daysInStage(): int
    {
        return (int) ($this->stage_changed_at?->diffInDays(now()) ?? 0);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('stage', [LeadStage::Ganado, LeadStage::Perdido]);
    }
}
