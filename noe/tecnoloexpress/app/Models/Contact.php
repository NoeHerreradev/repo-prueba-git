<?php

namespace App\Models;

use App\Enums\ContactType;
use App\Enums\LeadSource;
use App\Enums\PropertyOperation;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'type', 'first_name', 'last_name', 'email', 'phone', 'whatsapp',
    'document_type', 'document_number', 'address', 'city', 'source',
    'assigned_agent_id', 'budget_min', 'budget_max', 'pref_operation',
    'pref_property_type', 'pref_city', 'pref_bedrooms_min', 'notes',
])]
class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => ContactType::class,
            'source' => LeadSource::class,
            'pref_operation' => PropertyOperation::class,
            'pref_property_type' => PropertyType::class,
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    /** Inmuebles de los que este contacto es propietario. */
    public function ownedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'client_contact_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subjectable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'subjectable');
    }

    /**
     * Inmuebles disponibles que encajan con las preferencias del contacto.
     * Cada preferencia vacía simplemente no filtra.
     */
    public function matchingProperties(): Builder
    {
        return Property::query()
            ->where('status', \App\Enums\PropertyStatus::Disponible)
            ->when($this->pref_property_type, fn (Builder $q) => $q->where('type', $this->pref_property_type))
            ->when($this->pref_city, fn (Builder $q) => $q->where('city', 'like', "%{$this->pref_city}%"))
            ->when($this->pref_bedrooms_min, fn (Builder $q) => $q->where('bedrooms', '>=', $this->pref_bedrooms_min))
            ->when($this->pref_operation, fn (Builder $q) => $q->where(function (Builder $sub) {
                $sub->where('operation', $this->pref_operation)
                    ->orWhere('operation', PropertyOperation::Ambos);
            }))
            ->when($this->budget_min, fn (Builder $q) => $q->where('price', '>=', $this->budget_min))
            ->when($this->budget_max, fn (Builder $q) => $q->where('price', '<=', $this->budget_max));
    }
}
