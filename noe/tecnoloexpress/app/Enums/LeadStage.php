<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LeadStage: string implements HasLabel, HasColor, HasIcon
{
    case Nuevo = 'nuevo';
    case Contactado = 'contactado';
    case Calificado = 'calificado';
    case Visita = 'visita';
    case Oferta = 'oferta';
    case Negociacion = 'negociacion';
    case Ganado = 'ganado';
    case Perdido = 'perdido';

    public function getLabel(): string
    {
        return match ($this) {
            self::Nuevo => 'Nuevo',
            self::Contactado => 'Contactado',
            self::Calificado => 'Calificado',
            self::Visita => 'Visita agendada',
            self::Oferta => 'Oferta',
            self::Negociacion => 'Negociación',
            self::Ganado => 'Ganado',
            self::Perdido => 'Perdido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Nuevo => 'gray',
            self::Contactado => 'info',
            self::Calificado => 'info',
            self::Visita => 'warning',
            self::Oferta => 'warning',
            self::Negociacion => 'warning',
            self::Ganado => 'success',
            self::Perdido => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Nuevo => 'heroicon-o-sparkles',
            self::Contactado => 'heroicon-o-phone',
            self::Calificado => 'heroicon-o-check-badge',
            self::Visita => 'heroicon-o-calendar-days',
            self::Oferta => 'heroicon-o-document-text',
            self::Negociacion => 'heroicon-o-chat-bubble-left-right',
            self::Ganado => 'heroicon-o-trophy',
            self::Perdido => 'heroicon-o-x-circle',
        };
    }

    /** Probabilidad de cierre sugerida por etapa. */
    public function defaultProbability(): int
    {
        return match ($this) {
            self::Nuevo => 10,
            self::Contactado => 20,
            self::Calificado => 40,
            self::Visita => 55,
            self::Oferta => 70,
            self::Negociacion => 85,
            self::Ganado => 100,
            self::Perdido => 0,
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::Ganado && $this !== self::Perdido;
    }

    /** Etapas que forman el embudo visible (excluye los cierres). */
    public static function funnel(): array
    {
        return [self::Nuevo, self::Contactado, self::Calificado, self::Visita, self::Oferta, self::Negociacion];
    }
}
