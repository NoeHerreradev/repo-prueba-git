<?php

namespace App\Filament\Widgets;

use App\Enums\CommissionStatus;
use App\Models\Commission;
use Filament\Widgets\ChartWidget;

class CommissionsByAgentChart extends ChartWidget
{
    protected ?string $heading = 'Comisiones por agente';

    protected ?string $description = 'Año en curso';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    /** Solo dirección ve la comparativa entre agentes. */
    public static function canView(): bool
    {
        return auth()->user()?->seesAllRecords() ?? false;
    }

    protected function getData(): array
    {
        $rows = Commission::query()
            ->join('users', 'users.id', '=', 'commissions.user_id')
            ->whereYear('commissions.created_at', now()->year)
            ->selectRaw('users.name as agente')
            ->selectRaw('sum(case when commissions.status = ? then commissions.amount else 0 end) as pagado', [CommissionStatus::Pagada->value])
            ->selectRaw('sum(case when commissions.status != ? then commissions.amount else 0 end) as pendiente', [CommissionStatus::Pagada->value])
            ->groupBy('users.id', 'users.name')
            ->orderByRaw('sum(commissions.amount) desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pagadas',
                    'data' => $rows->pluck('pagado')->map(fn ($v) => (float) $v)->all(),
                    'backgroundColor' => '#22c55e',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Pendientes',
                    'data' => $rows->pluck('pendiente')->map(fn ($v) => (float) $v)->all(),
                    'backgroundColor' => '#f59e0b',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $rows->pluck('agente')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true],
            ],
        ];
    }
}
