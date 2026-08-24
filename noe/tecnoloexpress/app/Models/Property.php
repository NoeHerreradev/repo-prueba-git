<?php

namespace App\Models;

use App\Enums\PropertyOperation;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'code', 'title', 'slug', 'description', 'operation', 'type', 'status',
    'price', 'rent_price', 'currency', 'maintenance_fee',
    'bedrooms', 'bathrooms', 'parking_spaces', 'area_built', 'area_lot', 'year_built', 'floor',
    'address', 'neighborhood', 'city', 'state', 'postal_code', 'latitude', 'longitude', 'show_exact_address',
    'owner_id', 'agent_id', 'commission_percent', 'exclusive', 'captured_at',
    'published', 'featured',
])]
class Property extends Model
{
    use HasFactory, SoftDeletes;

    /** Espejo de los defaults de la migración, para modelos aún no releídos de BD. */
    protected $attributes = [
        'currency' => 'USD',
        'status' => 'disponible',
        'commission_percent' => 3,
    ];

    protected function casts(): array
    {
        return [
            'operation' => PropertyOperation::class,
            'type' => PropertyType::class,
            'status' => PropertyStatus::class,
            'price' => 'decimal:2',
            'rent_price' => 'decimal:2',
            'maintenance_fee' => 'decimal:2',
            'area_built' => 'decimal:2',
            'area_lot' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'show_exact_address' => 'boolean',
            'exclusive' => 'boolean',
            'published' => 'boolean',
            'featured' => 'boolean',
            'captured_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Property $property) {
            $property->code ??= static::nextCode();
            $property->slug ??= static::uniqueSlug($property->title);
        });
    }

    /** Referencia correlativa tipo PROP-0001. */
    public static function nextCode(): string
    {
        $last = static::withTrashed()->max('id') ?? 0;

        return 'PROP-'.str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    /** Slug único; añade sufijo numérico si ya existe. */
    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'inmueble';
        $slug = $base;
        $i = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'owner_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
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
        return $this->hasMany(Contract::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subjectable');
    }

    /** Imagen de portada, o la primera disponible. */
    public function coverImage(): ?PropertyImage
    {
        return $this->images->firstWhere('is_cover', true) ?? $this->images->first();
    }

    /** URL pública de la portada, con imagen genérica de respaldo. */
    public function coverUrl(): string
    {
        $path = $this->coverImage()?->path;

        return $path ? Storage::disk('public')->url($path) : asset('images/placeholder.svg');
    }

    /**
     * Dirección tal como puede mostrarse en el portal público: la exacta solo
     * si el propietario lo autorizó, si no únicamente barrio y ciudad.
     */
    public function publicAddress(): string
    {
        $parts = $this->show_exact_address
            ? [$this->address, $this->neighborhood, $this->city]
            : [$this->neighborhood, $this->city];

        return collect($parts)->filter()->join(', ');
    }

    /** Precio a mostrar según la operación. */
    public function displayPrice(): ?string
    {
        $amount = $this->operation === PropertyOperation::Alquiler
            ? $this->rent_price
            : $this->price;

        if ($amount === null) {
            return null;
        }

        $formatted = $this->currency.' '.number_format((float) $amount, 0, ',', '.');

        return $this->operation === PropertyOperation::Alquiler
            ? "{$formatted} /mes"
            : $formatted;
    }

    /** Días que lleva el inmueble en cartera desde su captación. */
    public function daysOnMarket(): ?int
    {
        return $this->captured_at ? (int) floor($this->captured_at->diffInDays(now())) : null;
    }

    /** Comisión estimada de la agencia sobre el precio de venta. */
    public function estimatedCommission(): float
    {
        return (float) $this->price * ((float) $this->commission_percent / 100);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)
            ->whereIn('status', [PropertyStatus::Disponible, PropertyStatus::Reservado]);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', PropertyStatus::Disponible);
    }
}
