<?php

namespace App\Filament\Pages;

use App\Enums\LeadStage;
use App\Models\Lead;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use UnitEnum;

class Pipeline extends Page
{
    protected string $view = 'filament.pages.pipeline';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static string|UnitEnum|null $navigationGroup = 'Comercial';

    protected static ?string $navigationLabel = 'Embudo';

    protected static ?string $title = 'Embudo de ventas';

    protected static ?int $navigationSort = 0;

    /** Filtro de agente: vacío = todos los que el usuario puede ver. */
    public ?string $agentFilter = null;

    /** Etapas que se muestran como columnas del tablero. */
    public function stages(): array
    {
        return [...LeadStage::funnel(), LeadStage::Ganado, LeadStage::Perdido];
    }

    /** @return Collection<string, Collection<int, Lead>> */
    #[Computed]
    public function leadsByStage(): Collection
    {
        return $this->baseQuery()
            ->with(['contact', 'property', 'agent'])
            ->orderByDesc('expected_value')
            ->get()
            ->groupBy(fn (Lead $lead) => $lead->stage->value);
    }

    #[Computed]
    public function agents(): Collection
    {
        return User::where('active', true)->orderBy('name')->pluck('name', 'id');
    }

    /** Mueve un lead a otra etapa desde el tablero. */
    public function moveLead(int $leadId, string $stage): void
    {
        $newStage = LeadStage::tryFrom($stage);
        $lead = $this->baseQuery()->find($leadId);

        if (! $newStage || ! $lead || $lead->stage === $newStage) {
            return;
        }

        // Marcar como perdido exige un motivo, así que se hace desde la ficha.
        if ($newStage === LeadStage::Perdido && blank($lead->lost_reason)) {
            unset($this->leadsByStage);

            Notification::make()
                ->title('Indica el motivo de pérdida')
                ->body('Abre la ficha del lead para registrar por qué se perdió.')
                ->warning()
                ->send();

            return;
        }

        $lead->update([
            'stage' => $newStage,
            'probability' => $newStage->defaultProbability(),
        ]);

        unset($this->leadsByStage);

        Notification::make()
            ->title($lead->contact?->full_name.' → '.$newStage->getLabel())
            ->success()
            ->send();
    }

    /** Total del valor esperado de una columna. */
    public function stageTotal(LeadStage $stage): float
    {
        return (float) ($this->leadsByStage[$stage->value]?->sum('expected_value') ?? 0);
    }

    public function getSubheading(): ?string
    {
        $open = $this->leadsByStage
            ->reject(fn (Collection $leads, string $stage) => ! LeadStage::from($stage)->isOpen())
            ->flatten();

        return sprintf(
            '%d oportunidades abiertas · valor ponderado $%s',
            $open->count(),
            number_format($open->sum(fn (Lead $lead) => $lead->weightedValue()), 0, ',', '.'),
        );
    }

    /** Un agente solo ve su propio embudo; los demás pueden filtrar. */
    protected function baseQuery(): Builder
    {
        return Lead::query()
            ->when(
                ! auth()->user()?->seesAllRecords(),
                fn (Builder $q) => $q->where('assigned_agent_id', auth()->id()),
                fn (Builder $q) => $q->when($this->agentFilter, fn (Builder $q2) => $q2->where('assigned_agent_id', $this->agentFilter)),
            );
    }
}
