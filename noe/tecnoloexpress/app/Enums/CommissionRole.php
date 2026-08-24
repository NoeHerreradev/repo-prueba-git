<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CommissionRole: string implements HasLabel
{
    case Captador = 'captador';
    case Vendedor = 'vendedor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Captador => 'Captador',
            self::Vendedor => 'Vendedor / cierre',
        };
    }

    /** Reparto por defecto de la comisión de la agencia. */
    public function defaultShare(): float
    {
        return 50.0;
    }
}
