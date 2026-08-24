<?php

namespace App\Filament\Resources\Visits\Tables;

use App\Enums\VisitStatus;
use App\Models\Visit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Visit $record) => $record->scheduled_at?->diffForHumans()),

                TextColumn::make('property.title')
                    ->label('Inmueble')
                    ->searchable()
                    ->wrap()
                    ->limit(40)
                    ->description(fn (Visit $record) => $record->property?->code),

                TextColumn::make('contact.first_name')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Visit $record) => $record->contact?->full_name)
                    ->searchable(['contacts.first_name', 'contacts.last_name'])
                    ->description(fn (Visit $record) => $record->contact?->phone),

                TextColumn::make('agent.name')
                    ->label('Agente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('interest_rating')
                    ->label('Interés')
                    ->alignCenter()
                    ->formatStateUsing(fn (?int $state) => $state ? str_repeat('★', $state).str_repeat('☆', 5 - $state) : '—')
                    ->sortable(),

                TextColumn::make('feedback')
                    ->label('Comentarios')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('duration_minutes')
                    ->label('Duración')
                    ->suffix(' min')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->groups([
                Group::make('scheduled_at')->label('Fecha')->date()->collapsible(),
                Group::make('agent.name')->label('Agente')->collapsible(),
                Group::make('status')->label('Estado')->collapsible(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(VisitStatus::class)
                    ->multiple(),

                SelectFilter::make('agent_id')
                    ->label('Agente')
                    ->relationship('agent', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('upcoming')
                    ->label('Próximas')
                    ->query(fn (Builder $query) => $query->upcoming()),

                Filter::make('this_week')
                    ->label('Esta semana')
                    ->query(fn (Builder $query) => $query
                        ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->recordActions([
                Action::make('complete')
                    ->label('Registrar resultado')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('success')
                    ->visible(fn (Visit $record) => in_array($record->status, [
                        VisitStatus::Programada,
                        VisitStatus::Confirmada,
                    ], true))
                    ->schema([
                        Select::make('interest_rating')
                            ->label('Nivel de interés')
                            ->options([
                                1 => '★☆☆☆☆ Muy bajo',
                                2 => '★★☆☆☆ Bajo',
                                3 => '★★★☆☆ Medio',
                                4 => '★★★★☆ Alto',
                                5 => '★★★★★ Muy alto',
                            ])
                            ->required(),

                        Textarea::make('feedback')
                            ->label('Comentarios del cliente')
                            ->rows(4),
                    ])
                    ->action(function (Visit $record, array $data) {
                        $record->update([
                            'status' => VisitStatus::Realizada,
                            'interest_rating' => $data['interest_rating'],
                            'feedback' => $data['feedback'] ?? null,
                        ]);

                        // La visita realizada queda registrada en la ficha del lead.
                        $record->lead?->activities()->create([
                            'type' => \App\Enums\ActivityType::Reunion,
                            'subject' => 'Visita a '.$record->property?->code,
                            'notes' => $data['feedback'] ?? null,
                            'occurred_at' => $record->scheduled_at,
                        ]);

                        Notification::make()->title('Visita registrada')->success()->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
