<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStage;
use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsFunnelChart extends ChartWidget
{
    protected ?string $heading = 'Embudo de ventas';

    protected ?string $description = 'Oportunidades abiertas por etapa';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $stages = LeadStage::funnel();

        $counts = Lead::query()
            ->when(
                ! auth()->user()?->seesAllRecords(),
                fn ($q) => $q->where('assigned_agent_id', auth()->id()),
            )
            ->open()
            ->selectRaw('stage, count(*) as total, sum(expected_value) as valor')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        return [
            'datasets' => [
                [
                    'label' => 'Oportunidades',
                    'data' => array_map(fn (LeadStage $s) => (int) ($counts[$s->value]->total ?? 0), $stages),
                    'backgroundColor' => '#6366f1',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => array_map(fn (LeadStage $s) => $s->getLabel(), $stages),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['ticks' => ['precision' => 0]],
            ],
        ];
    }
}
