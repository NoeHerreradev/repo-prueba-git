<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CommissionStatus: string implements HasLabel, HasColor
{
    case Pendiente = 'pendiente';
    case Aprobada = 'aprobada';
    case Pagada = 'pagada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Aprobada => 'Aprobada',
            self::Pagada => 'Pagada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'gray',
            self::Aprobada => 'warning',
            self::Pagada => 'success',
        };
    }
}
