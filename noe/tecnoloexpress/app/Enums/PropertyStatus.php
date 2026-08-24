<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PropertyStatus: string implements HasLabel, HasColor
{
    case Borrador = 'borrador';
    case Disponible = 'disponible';
    case Reservado = 'reservado';
    case Vendido = 'vendido';
    case Alquilado = 'alquilado';
    case Retirado = 'retirado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Disponible => 'Disponible',
            self::Reservado => 'Reservado',
            self::Vendido => 'Vendido',
            self::Alquilado => 'Alquilado',
            self::Retirado => 'Retirado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Disponible => 'success',
            self::Reservado => 'warning',
            self::Vendido => 'info',
            self::Alquilado => 'info',
            self::Retirado => 'danger',
        };
    }

    /** Estados en los que la propiedad ya no se puede comercializar. */
    public function isClosed(): bool
    {
        return in_array($this, [self::Vendido, self::Alquilado, self::Retirado], true);
    }
}
