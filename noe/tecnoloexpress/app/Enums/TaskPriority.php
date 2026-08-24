<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TaskPriority: string implements HasLabel, HasColor
{
    case Baja = 'baja';
    case Media = 'media';
    case Alta = 'alta';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baja => 'Baja',
            self::Media => 'Media',
            self::Alta => 'Alta',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baja => 'gray',
            self::Media => 'warning',
            self::Alta => 'danger',
        };
    }
}
