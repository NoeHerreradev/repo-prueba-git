<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('contact.first_name')
                            ->label('Contacto')
                            ->state(fn ($record) => $record->contact?->full_name)
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->url(fn ($record) => $record->contact
                                ? \App\Filament\Resources\Contacts\ContactResource::getUrl('view', ['record' => $record->contact])
                                : null)
                            ->columnSpan(2),

                        TextEntry::make('stage')->label('Etapa')->badge(),
                        TextEntry::make('source')->label('Origen')->badge()->color('gray'),

                        TextEntry::make('property.title')
                            ->label('Servicio de interés')
                            ->placeholder('Consulta general')
                            ->url(fn ($record) => $record->property
                                ? \App\Filament\Resources\Properties\PropertyResource::getUrl('view', ['record' => $record->property])
                                : null)
                            ->columnSpan(2),

                        TextEntry::make('expected_value')->label('Valor estimado')->money('USD')->placeholder('—'),
                        TextEntry::make('probability')->label('Probabilidad')->suffix('%'),

                        TextEntry::make('agent.name')->label('Agente')->placeholder('Sin asignar'),
                        TextEntry::make('expected_close_date')->label('Cierre estimado')->date('d/m/Y')->placeholder('—'),
                        TextEntry::make('stage_changed_at')
                            ->label('Tiempo en etapa')
                            ->state(fn ($record) => $record->daysInStage().' días'),
                        TextEntry::make('lost_reason')
                            ->label('Motivo de pérdida')
                            ->placeholder('—')
                            ->visible(fn ($record) => filled($record->lost_reason)),

                        TextEntry::make('notes')->label('Notas')->placeholder('Sin notas')->columnSpanFull(),
                    ]),

                Grid::make(2)->columnSpanFull()->schema([
                    Section::make('Historial del embudo')
                        ->schema([
                            RepeatableEntry::make('stageHistories')
                                ->hiddenLabel()
                                ->contained(false)
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextEntry::make('to_stage')->label('Etapa')->badge(),
                                        TextEntry::make('user.name')->label('Por')->placeholder('Sistema'),
                                        TextEntry::make('changed_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                                    ]),
                                ]),
                        ]),

                    Section::make('Actividad reciente')
                        ->schema([
                            RepeatableEntry::make('activities')
                                ->hiddenLabel()
                                ->contained(false)
                                ->placeholder('Sin actividades registradas')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextEntry::make('type')->label('Tipo')->badge(),
                                        TextEntry::make('subject')->label('Asunto'),
                                        TextEntry::make('occurred_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                                    ]),
                                ]),
                        ]),
                ]),
            ]);
    }
}
