<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel, HasColor
{
    case Admin = 'admin';
    case Gerente = 'gerente';
    case Agente = 'agente';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Gerente => 'Gerente',
            self::Agente => 'Agente',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Gerente => 'warning',
            self::Agente => 'info',
        };
    }
}
