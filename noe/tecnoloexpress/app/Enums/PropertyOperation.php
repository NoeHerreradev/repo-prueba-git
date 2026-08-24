<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PropertyOperation: string implements HasLabel, HasColor
{
    case Venta = 'venta';
    case Alquiler = 'alquiler';
    case Ambos = 'ambos';

    public function getLabel(): string
    {
        return match ($this) {
            self::Venta => 'Venta',
            self::Alquiler => 'Alquiler',
            self::Ambos => 'Venta y alquiler',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Venta => 'success',
            self::Alquiler => 'info',
            self::Ambos => 'warning',
        };
    }

    public function includesSale(): bool
    {
        return $this === self::Venta || $this === self::Ambos;
    }

    public function includesRent(): bool
    {
        return $this === self::Alquiler || $this === self::Ambos;
    }
}
