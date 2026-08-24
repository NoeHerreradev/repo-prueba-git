<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PropertyType: string implements HasLabel, HasIcon
{
    case Casa = 'casa';
    case Apartamento = 'apartamento';
    case Local = 'local';
    case Oficina = 'oficina';
    case Terreno = 'terreno';
    case Bodega = 'bodega';
    case Edificio = 'edificio';

    public function getLabel(): string
    {
        return match ($this) {
            self::Casa => 'Casa',
            self::Apartamento => 'Apartamento',
            self::Local => 'Local comercial',
            self::Oficina => 'Oficina',
            self::Terreno => 'Terreno',
            self::Bodega => 'Bodega',
            self::Edificio => 'Edificio',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Casa => 'heroicon-o-home',
            self::Apartamento => 'heroicon-o-building-office-2',
            self::Local => 'heroicon-o-shopping-bag',
            self::Oficina => 'heroicon-o-briefcase',
            self::Terreno => 'heroicon-o-map',
            self::Bodega => 'heroicon-o-archive-box',
            self::Edificio => 'heroicon-o-building-office',
        };
    }

    /** Los terrenos no tienen habitaciones ni baños. */
    public function hasRooms(): bool
    {
        return $this !== self::Terreno;
    }
}
