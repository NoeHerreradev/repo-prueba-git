<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContractType: string implements HasLabel, HasColor
{
    case Reserva = 'reserva';
    case Venta = 'venta';
    case Alquiler = 'alquiler';

    public function getLabel(): string
    {
        return match ($this) {
            self::Reserva => 'Reserva',
            self::Venta => 'Venta',
            self::Alquiler => 'Alquiler',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Reserva => 'warning',
            self::Venta => 'success',
            self::Alquiler => 'info',
        };
    }

    /** Estado al que pasa la propiedad cuando el contrato se activa. */
    public function resultingPropertyStatus(): PropertyStatus
    {
        return match ($this) {
            self::Reserva => PropertyStatus::Reservado,
            self::Venta => PropertyStatus::Vendido,
            self::Alquiler => PropertyStatus::Alquilado,
        };
    }
}
