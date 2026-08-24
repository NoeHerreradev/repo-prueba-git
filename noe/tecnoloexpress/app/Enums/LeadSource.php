<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LeadSource: string implements HasLabel
{
    case Web = 'web';
    case Portal = 'portal';
    case Referido = 'referido';
    case Llamada = 'llamada';
    case Redes = 'redes';
    case Feria = 'feria';
    case WalkIn = 'walk_in';
    case Otro = 'otro';

    public function getLabel(): string
    {
        return match ($this) {
            self::Web => 'Sitio web',
            self::Portal => 'Portal inmobiliario',
            self::Referido => 'Referido',
            self::Llamada => 'Llamada entrante',
            self::Redes => 'Redes sociales',
            self::Feria => 'Feria / evento',
            self::WalkIn => 'Visita a oficina',
            self::Otro => 'Otro',
        };
    }
}
