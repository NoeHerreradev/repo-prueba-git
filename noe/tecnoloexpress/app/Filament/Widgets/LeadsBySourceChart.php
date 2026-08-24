<?php

namespace App\Filament\Widgets;

use App\Enums\LeadSource;
use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsBySourceChart extends ChartWidget
{
    protected ?string $heading = 'Origen de los leads';

    protected ?string $description = 'Últimos 90 días';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Lead::query()
            ->when(
                ! auth()->user()?->seesAllRecords(),
                fn ($q) => $q->where('assigned_agent_id', auth()->id()),
            )
            ->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('source, count(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        $sources = $counts->keys()
            ->map(fn (string $value) => LeadSource::tryFrom($value))
            ->filter()
            ->values();

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $sources->map(fn (LeadSource $s) => (int) $counts[$s->value])->all(),
                    'backgroundColor' => [
                        '#6366f1', '#22c55e', '#f59e0b', '#ef4444',
                        '#06b6d4', '#a855f7', '#ec4899', '#64748b',
                    ],
                ],
            ],
            'labels' => $sources->map(fn (LeadSource $s) => $s->getLabel())->all(),
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
