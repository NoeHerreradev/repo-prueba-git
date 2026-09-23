<?php

namespace App\Services;

use App\Enums\CommissionStatus;
use App\Enums\ContractStatus;
use App\Enums\LeadSource;
use App\Enums\LeadStage;
use App\Enums\PropertyStatus;
use App\Enums\VisitStatus;
use App\Models\Commission;
use App\Models\Contract;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Setting;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;

class MetricsReportService
{
    /**
     * Recopila todas las métricas operativas y comerciales del mes seleccionado.
     */
    public function getMonthlyMetrics(int $year, int $month, ?User $user = null): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $periodLabel = ($monthNames[$month] ?? 'Mes '.$month).' de '.$year;

        // Filtro por rol: si el usuario es agente, solo ve sus datos.
        $seesAll = $user?->seesAllRecords() ?? true;
        $agentId = $user?->id;

        // 1. Catálogo de Servicios
        $propertiesQuery = Property::query()
            ->when(! $seesAll && $agentId, fn ($q) => $q->where('agent_id', $agentId));

        $availableProperties = (clone $propertiesQuery)->where('status', PropertyStatus::Disponible)->count();
        $availablePortfolio = (clone $propertiesQuery)->where('status', PropertyStatus::Disponible)->sum('price');
        $totalProperties = (clone $propertiesQuery)->count();

        // 2. Leads y Oportunidades del mes
        $leadsQuery = Lead::query()
            ->when(! $seesAll && $agentId, fn ($q) => $q->where('assigned_agent_id', $agentId));

        $leadsCreatedInMonth = (clone $leadsQuery)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $openLeads = (clone $leadsQuery)->open()->get();
        $openLeadsCount = $openLeads->count();
        $openLeadsWeighted = $openLeads->sum(fn (Lead $l) => $l->weightedValue());

        // 3. Visitas / Citas del mes
        $visitsQuery = Visit::query()
            ->when(! $seesAll && $agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->whereBetween('scheduled_at', [$start, $end]);

        $visitsTotal = (clone $visitsQuery)->count();
        $visitsCompleted = (clone $visitsQuery)->where('status', VisitStatus::Realizada)->count();
        $visitsPending = (clone $visitsQuery)->whereIn('status', [VisitStatus::Programada, VisitStatus::Confirmada])->count();

        // 4. Cierres y Contratos del mes
        $contractsQuery = Contract::query()
            ->when(! $seesAll && $agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->whereBetween('signed_at', [$start, $end]);

        $contractsTotal = (clone $contractsQuery)->count();
        $contractsVolume = (clone $contractsQuery)->sum('amount');
        $contractsList = (clone $contractsQuery)
            ->with(['client', 'property', 'agent'])
            ->orderByDesc('signed_at')
            ->get();

        // 5. Comisiones del mes
        $commissionsQuery = Commission::query()
            ->when(! $seesAll && $agentId, fn ($q) => $q->where('user_id', $agentId))
            ->whereBetween('created_at', [$start, $end]);

        $commissionsTotal = (clone $commissionsQuery)->sum('amount');
        $commissionsPaid = (clone $commissionsQuery)->where('status', CommissionStatus::Pagada)->sum('amount');
        $commissionsPending = (clone $commissionsQuery)->where('status', '!=', CommissionStatus::Pagada)->sum('amount');

        // 6. Tasa de conversión
        $conversionRate = $leadsCreatedInMonth > 0
            ? round(($contractsTotal / $leadsCreatedInMonth) * 100, 1)
            : 0;

        // Desglose de leads por etapa
        $leadsByStage = [];
        $allStages = [...LeadStage::funnel(), LeadStage::Ganado, LeadStage::Perdido];
        foreach ($allStages as $stage) {
            $count = (clone $leadsQuery)
                ->where('stage', $stage)
                ->when($stage === LeadStage::Ganado || $stage === LeadStage::Perdido, fn ($q) => $q->whereBetween('updated_at', [$start, $end]))
                ->count();
            $leadsByStage[] = [
                'stage' => $stage->getLabel(),
                'color' => $stage->getColor(),
                'count' => $count,
            ];
        }

        // Desglose de leads por origen en el mes
        $leadsBySource = [];
        foreach (LeadSource::cases() as $source) {
            $count = (clone $leadsQuery)
                ->where('source', $source)
                ->whereBetween('created_at', [$start, $end])
                ->count();
            if ($count > 0 || true) {
                $leadsBySource[] = [
                    'source' => $source->getLabel(),
                    'count' => $count,
                ];
            }
        }

        // Rendimiento por Asesor / Agente (si es admin/gerente)
        $agentsPerformance = [];
        if ($seesAll) {
            $agents = User::where('active', true)->orderBy('name')->get();
            foreach ($agents as $agent) {
                $agentLeads = Lead::where('assigned_agent_id', $agent->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
                $agentContracts = Contract::where('agent_id', $agent->id)
                    ->whereBetween('signed_at', [$start, $end]);
                $agentContractsCount = (clone $agentContracts)->count();
                $agentContractsAmount = (clone $agentContracts)->sum('amount');
                $agentCommissions = Commission::where('user_id', $agent->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount');

                $agentsPerformance[] = [
                    'name' => $agent->name,
                    'role' => $agent->role->getLabel(),
                    'leads_count' => $agentLeads,
                    'contracts_count' => $agentContractsCount,
                    'volume' => $agentContractsAmount,
                    'commission' => $agentCommissions,
                ];
            }
        }

        return [
            'periodLabel' => $periodLabel,
            'year' => $year,
            'month' => $month,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i'),
            'companyName' => Setting::get('company_name', 'Tecnolo CRM'),
            'companyEmail' => Setting::get('company_email', ''),
            'companyPhone' => Setting::get('company_phone', ''),
            'kpis' => [
                'availableProperties' => $availableProperties,
                'availablePortfolio' => $availablePortfolio,
                'totalProperties' => $totalProperties,
                'leadsCreatedInMonth' => $leadsCreatedInMonth,
                'openLeadsCount' => $openLeadsCount,
                'openLeadsWeighted' => $openLeadsWeighted,
                'visitsTotal' => $visitsTotal,
                'visitsCompleted' => $visitsCompleted,
                'visitsPending' => $visitsPending,
                'contractsTotal' => $contractsTotal,
                'contractsVolume' => $contractsVolume,
                'commissionsTotal' => $commissionsTotal,
                'commissionsPaid' => $commissionsPaid,
                'commissionsPending' => $commissionsPending,
                'conversionRate' => $conversionRate,
            ],
            'leadsByStage' => $leadsByStage,
            'leadsBySource' => $leadsBySource,
            'agentsPerformance' => $agentsPerformance,
            'contractsList' => $contractsList,
            'userName' => $user?->name ?? 'Administrador',
        ];
    }
}
