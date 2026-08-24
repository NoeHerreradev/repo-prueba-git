<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum VisitStatus: string implements HasLabel, HasColor
{
    case Programada = 'programada';
    case Confirmada = 'confirmada';
    case Realizada = 'realizada';
    case Cancelada = 'cancelada';
    case NoAsistio = 'no_asistio';

    public function getLabel(): string
    {
        return match ($this) {
            self::Programada => 'Programada',
            self::Confirmada => 'Confirmada',
            self::Realizada => 'Realizada',
            self::Cancelada => 'Cancelada',
            self::NoAsistio => 'No asistió',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Programada => 'gray',
            self::Confirmada => 'info',
            self::Realizada => 'success',
            self::Cancelada => 'danger',
            self::NoAsistio => 'warning',
        };
    }
}
