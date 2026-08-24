<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContractStatus: string implements HasLabel, HasColor
{
    case Borrador = 'borrador';
    case PendienteFirma = 'pendiente_firma';
    case Activo = 'activo';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::PendienteFirma => 'Pendiente de firma',
            self::Activo => 'Activo',
            self::Finalizado => 'Finalizado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::PendienteFirma => 'warning',
            self::Activo => 'success',
            self::Finalizado => 'info',
            self::Cancelado => 'danger',
        };
    }

    /** Un contrato en estos estados bloquea la propiedad. */
    public function locksProperty(): bool
    {
        return in_array($this, [self::Activo, self::Finalizado], true);
    }
}
