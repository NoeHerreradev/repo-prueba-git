<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadSource;
use App\Enums\LeadStage;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contact.first_name')
                    ->label('Contacto')
                    ->formatStateUsing(fn ($record) => $record->contact?->full_name)
                    ->searchable(['contacts.first_name', 'contacts.last_name'])
                    ->weight('medium')
                    ->description(fn ($record) => $record->contact?->phone),

                TextColumn::make('property.title')
                    ->label('Servicio')
                    ->placeholder('Consulta general')
                    ->searchable()
                    ->limit(35)
                    ->description(fn ($record) => $record->property?->code),

                TextColumn::make('stage')
                    ->label('Etapa')
                    ->badge()
                    ->sortable(),

                TextColumn::make('probability')
                    ->label('Prob.')
                    ->suffix('%')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('expected_value')
                    ->label('Valor')
                    ->money('USD')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total')->money('USD')),

                TextColumn::make('weighted')
                    ->label('Ponderado')
                    ->state(fn ($record) => $record->weightedValue())
                    ->money('USD')
                    ->tooltip('Valor estimado × probabilidad de cierre')
                    ->toggleable(),

                TextColumn::make('source')
                    ->label('Origen')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('agent.name')
                    ->label('Agente')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stage_changed_at')
                    ->label('En etapa')
                    ->state(fn ($record) => $record->daysInStage().' días')
                    // Un lead estancado más de 15 días necesita atención.
                    ->color(fn ($record) => match (true) {
                        ! $record->stage->isOpen() => 'gray',
                        $record->daysInStage() > 15 => 'danger',
                        $record->daysInStage() > 7 => 'warning',
                        default => 'success',
                    })
                    ->badge()
                    ->sortable(),

                TextColumn::make('expected_close_date')
                    ->label('Cierre est.')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('stage')->label('Etapa')->collapsible(),
                Group::make('source')->label('Origen')->collapsible(),
                Group::make('agent.name')->label('Agente')->collapsible(),
            ])
            ->filters([
                SelectFilter::make('stage')
                    ->label('Etapa')
                    ->options(LeadStage::class)
                    ->multiple(),

                SelectFilter::make('source')
                    ->label('Origen')
                    ->options(LeadSource::class)
                    ->multiple(),

                SelectFilter::make('assigned_agent_id')
                    ->label('Agente')
                    ->relationship('agent', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('open')
                    ->label('Solo oportunidades abiertas')
                    ->query(fn (Builder $query) => $query->open())
                    ->default(),

                Filter::make('stalled')
                    ->label('Estancados (+15 días)')
                    ->query(fn (Builder $query) => $query
                        ->open()
                        ->where('stage_changed_at', '<', now()->subDays(15))),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('advance')
                    ->label('Avanzar')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->color('success')
                    ->visible(fn (Lead $record) => $record->stage->isOpen())
                    ->schema([
                        Select::make('stage')
                            ->label('Nueva etapa')
                            ->options(LeadStage::class)
                            ->required(),
                        Textarea::make('note')
                            ->label('Comentario (se guarda como actividad)')
                            ->rows(3),
                    ])
                    ->action(function (Lead $record, array $data) {
                        $stage = LeadStage::from($data['stage']);

                        $record->update([
                            'stage' => $stage,
                            'probability' => $stage->defaultProbability(),
                        ]);

                        if (filled($data['note'] ?? null)) {
                            $record->activities()->create([
                                'type' => \App\Enums\ActivityType::Nota,
                                'subject' => 'Cambio a etapa '.$stage->getLabel(),
                                'notes' => $data['note'],
                            ]);
                        }

                        Notification::make()
                            ->title('Lead movido a '.$stage->getLabel())
                            ->success()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('reassign')
                        ->label('Reasignar agente')
                        ->icon('heroicon-m-user-plus')
                        ->schema([
                            Select::make('assigned_agent_id')
                                ->label('Agente')
                                ->options(fn () => \App\Models\User::where('active', true)->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update(['assigned_agent_id' => $data['assigned_agent_id']]);

                            Notification::make()
                                ->title($records->count().' leads reasignados')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
