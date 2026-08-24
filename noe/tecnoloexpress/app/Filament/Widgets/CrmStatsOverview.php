<?php

namespace App\Filament\Widgets;

use App\Enums\CommissionStatus;
use App\Enums\ContractStatus;
use App\Enums\LeadStage;
use App\Enums\PropertyStatus;
use App\Enums\VisitStatus;
use App\Models\Commission;
use App\Models\Contract;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Visit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class CrmStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            $this->availablePropertiesStat(),
            $this->openLeadsStat(),
            $this->visitsThisWeekStat(),
            $this->closedThisMonthStat(),
            $this->commissionsPendingStat(),
            $this->conversionRateStat(),
        ];
    }

    protected function availablePropertiesStat(): Stat
    {
        $query = $this->scoped(Property::query(), 'agent_id');
        $available = (clone $query)->where('status', PropertyStatus::Disponible)->count();
        $portfolio = (clone $query)->where('status', PropertyStatus::Disponible)->sum('price');

        return Stat::make('Inmuebles disponibles', $available)
            ->description('Cartera: $'.number_format((float) $portfolio, 0, ',', '.'))
            ->descriptionIcon('heroicon-m-building-office-2')
            ->color('warning');
    }

    protected function openLeadsStat(): Stat
    {
        $leads = $this->scoped(Lead::query(), 'assigned_agent_id')->open()->get();
        $weighted = $leads->sum(fn (Lead $lead) => $lead->weightedValue());

        return Stat::make('Leads abiertos', $leads->count())
            ->description('Ponderado: $'.number_format($weighted, 0, ',', '.'))
            ->descriptionIcon('heroicon-m-funnel')
            ->color('info');
    }

    protected function visitsThisWeekStat(): Stat
    {
        $count = $this->scoped(Visit::query(), 'agent_id')
            ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereIn('status', [VisitStatus::Programada, VisitStatus::Confirmada])
            ->count();

        return Stat::make('Visitas esta semana', $count)
            ->description('Programadas o confirmadas')
            ->descriptionIcon('heroicon-m-calendar-days')
            ->color($count > 0 ? 'success' : 'gray');
    }

    protected function closedThisMonthStat(): Stat
    {
        $contracts = $this->scoped(Contract::query(), 'agent_id')
            ->whereIn('status', [ContractStatus::Activo, ContractStatus::Finalizado])
            ->whereMonth('signed_at', now()->month)
            ->whereYear('signed_at', now()->year);

        $count = (clone $contracts)->count();
        $volume = (clone $contracts)->sum('amount');

        return Stat::make('Cierres este mes', $count)
            ->description('Volumen: $'.number_format((float) $volume, 0, ',', '.'))
            ->descriptionIcon('heroicon-m-document-check')
            ->color('success');
    }

    protected function commissionsPendingStat(): Stat
    {
        $amount = $this->scoped(Commission::query(), 'user_id')
            ->where('status', '!=', CommissionStatus::Pagada)
            ->sum('amount');

        return Stat::make('Comisiones por pagar', '$'.number_format((float) $amount, 0, ',', '.'))
            ->description('Pendientes y aprobadas')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('danger');
    }

    /** Conversión de leads creados en los últimos 90 días. */
    protected function conversionRateStat(): Stat
    {
        $base = $this->scoped(Lead::query(), 'assigned_agent_id')
            ->where('created_at', '>=', now()->subDays(90));

        $total = (clone $base)->count();
        $won = (clone $base)->where('stage', LeadStage::Ganado)->count();
        $rate = $total > 0 ? round($won / $total * 100, 1) : 0;

        return Stat::make('Conversión (90 días)', $rate.'%')
            ->description("{$won} ganados de {$total} leads")
            ->descriptionIcon($rate >= 20 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($rate >= 20 ? 'success' : ($rate >= 10 ? 'warning' : 'danger'));
    }

    /** Un agente solo ve sus propias cifras. */
    protected function scoped(Builder $query, string $column): Builder
    {
        if (! auth()->user()?->seesAllRecords()) {
            $query->where($column, auth()->id());
        }

        return $query;
    }
}
