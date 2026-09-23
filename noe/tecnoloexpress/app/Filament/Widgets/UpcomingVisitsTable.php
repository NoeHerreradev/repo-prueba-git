<?php

namespace App\Filament\Widgets;

use App\Enums\VisitStatus;
use App\Models\Visit;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingVisitsTable extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Próximas visitas')
            ->description('Agenda de los próximos 14 días')
            ->query(
                Visit::query()
                    ->with(['property', 'contact', 'agent'])
                    ->whereBetween('scheduled_at', [now(), now()->addDays(14)])
                    ->whereIn('status', [VisitStatus::Programada, VisitStatus::Confirmada])
                    ->when(
                        ! auth()->user()?->seesAllRecords(),
                        fn ($q) => $q->where('agent_id', auth()->id()),
                    )
            )
            ->defaultSort('scheduled_at')
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No hay visitas programadas')
            ->emptyStateDescription('Agenda una visita desde la ficha de un lead o de un servicio.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Cuándo')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn (Visit $record) => $record->scheduled_at?->diffForHumans())
                    ->sortable(),

                TextColumn::make('property.title')
                    ->label('Servicio')
                    ->limit(40)
                    ->description(fn (Visit $record) => $record->property?->code),

                TextColumn::make('contact.first_name')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Visit $record) => $record->contact?->full_name)
                    ->description(fn (Visit $record) => $record->contact?->phone),

                TextColumn::make('agent.name')
                    ->label('Agente'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Abrir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Visit $record) => \App\Filament\Resources\Visits\VisitResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
