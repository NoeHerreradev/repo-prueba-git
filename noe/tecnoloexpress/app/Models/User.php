<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'allowed_modules', 'phone', 'avatar_path', 'commission_percent', 'active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const MODULES = [
        'pipeline' => 'Embudo comercial',
        'leads' => 'Leads / Oportunidades',
        'contacts' => 'Contactos',
        'visits' => 'Visitas',
        'contracts' => 'Contratos',
        'commissions' => 'Comisiones',
        'properties' => 'Servicios',
        'amenities' => 'Características / Amenidades',
        'users' => 'Gestión de Usuarios',
        'portal_settings' => 'Ajustes del Portal público',
        'form_settings' => 'Constructor de Formularios',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'allowed_modules' => 'array',
            'commission_percent' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /** Admins y gerentes ven todos los registros; los agentes solo los suyos. */
    public function seesAllRecords(): bool
    {
        return in_array($this->role, [UserRole::Admin, UserRole::Gerente], true);
    }

    public function hasModuleAccess(string $module): bool
    {
        if (! $this->active) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        if (empty($this->allowed_modules)) {
            return match ($module) {
                'users', 'portal_settings', 'form_settings' => false,
                default => true,
            };
        }

        return in_array($module, $this->allowed_modules, true);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'agent_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_agent_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'agent_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'agent_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
