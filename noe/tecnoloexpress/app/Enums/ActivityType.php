<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActivityType: string implements HasLabel, HasColor, HasIcon
{
    case Llamada = 'llamada';
    case Email = 'email';
    case Whatsapp = 'whatsapp';
    case Reunion = 'reunion';
    case Nota = 'nota';

    public function getLabel(): string
    {
        return match ($this) {
            self::Llamada => 'Llamada',
            self::Email => 'Email',
            self::Whatsapp => 'WhatsApp',
            self::Reunion => 'Reunión',
            self::Nota => 'Nota',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Llamada => 'info',
            self::Email => 'warning',
            self::Whatsapp => 'success',
            self::Reunion => 'danger',
            self::Nota => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Llamada => 'heroicon-o-phone',
            self::Email => 'heroicon-o-envelope',
            self::Whatsapp => 'heroicon-o-chat-bubble-bottom-center-text',
            self::Reunion => 'heroicon-o-users',
            self::Nota => 'heroicon-o-pencil-square',
        };
    }
}
