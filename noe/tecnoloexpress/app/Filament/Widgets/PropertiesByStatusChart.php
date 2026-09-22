<?php

namespace App\Filament\Widgets;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Filament\Widgets\ChartWidget;

class PropertiesByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Servicios por estado';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $counts = Property::query()
            ->when(
                ! auth()->user()?->seesAllRecords(),
                fn ($q) => $q->where('agent_id', auth()->id()),
            )
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = collect(PropertyStatus::cases())
            ->filter(fn (PropertyStatus $s) => isset($counts[$s->value]))
            ->values();

        return [
            'datasets' => [
                [
                    'label' => 'Servicios',
                    'data' => $statuses->map(fn (PropertyStatus $s) => (int) $counts[$s->value])->all(),
                    'backgroundColor' => $statuses->map(fn (PropertyStatus $s) => match ($s) {
                        PropertyStatus::Borrador => '#94a3b8',
                        PropertyStatus::Disponible => '#22c55e',
                        PropertyStatus::Reservado => '#f59e0b',
                        PropertyStatus::Vendido => '#6366f1',
                        PropertyStatus::Alquilado => '#06b6d4',
                        PropertyStatus::Retirado => '#ef4444',
                    })->all(),
                ],
            ],
            'labels' => $statuses->map(fn (PropertyStatus $s) => $s->getLabel())->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'right'],
            ],
        ];
    }
}
