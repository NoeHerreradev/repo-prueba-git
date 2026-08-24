<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContactType: string implements HasLabel, HasColor
{
    case Propietario = 'propietario';
    case Comprador = 'comprador';
    case Arrendatario = 'arrendatario';
    case Inversor = 'inversor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Propietario => 'Propietario',
            self::Comprador => 'Comprador',
            self::Arrendatario => 'Arrendatario',
            self::Inversor => 'Inversor',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Propietario => 'warning',
            self::Comprador => 'success',
            self::Arrendatario => 'info',
            self::Inversor => 'danger',
        };
    }

    /** Tipos que buscan inmueble y por tanto tienen preferencias de búsqueda. */
    public function isBuyer(): bool
    {
        return $this !== self::Propietario;
    }
}
