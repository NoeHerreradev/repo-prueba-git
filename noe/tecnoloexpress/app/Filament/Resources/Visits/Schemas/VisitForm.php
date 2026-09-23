<?php

namespace App\Filament\Resources\Visits\Schemas;

use App\Enums\PropertyStatus;
use App\Enums\VisitStatus;
use App\Models\Lead;
use App\Support\EnumValue;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class VisitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Agenda')
                    ->columns(2)
                    ->schema([
                        Select::make('property_id')
                            ->label('Servicio')
                            ->relationship('property', 'title', fn ($query) => $query
                                ->whereIn('status', [PropertyStatus::Disponible, PropertyStatus::Reservado]))
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} — {$record->title}")
                            ->searchable(['title', 'code', 'city'])
                            ->preload()
                            ->required(),

                        Select::make('lead_id')
                            ->label('Lead relacionado')
                            ->relationship('lead', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->contact?->full_name.' — '.($record->property?->code ?? 'general'))
                            ->searchable()
                            ->preload()
                            ->live()
                            // Al elegir el lead se heredan su contacto y su agente.
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                if (! $state) {
                                    return;
                                }

                                $lead = Lead::find($state);
                                $set('contact_id', $lead?->contact_id);
                                $set('agent_id', $lead?->assigned_agent_id);
                            }),

                        Select::make('contact_id')
                            ->label('Cliente')
                            ->relationship('contact', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable(['first_name', 'last_name', 'phone'])
                            ->preload(),

                        Select::make('agent_id')
                            ->label('Agente que acompaña')
                            ->relationship('agent', 'name', fn ($query) => $query->where('active', true))
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id()),

                        DateTimePicker::make('scheduled_at')
                            ->label('Fecha y hora')
                            ->seconds(false)
                            ->default(now()->addDay()->setTime(10, 0))
                            ->required(),

                        TextInput::make('duration_minutes')
                            ->label('Duración')
                            ->numeric()
                            ->suffix('minutos')
                            ->default(60),

                        Select::make('status')
                            ->label('Estado')
                            ->options(VisitStatus::class)
                            ->default(VisitStatus::Programada)
                            ->required()
                            ->live(),
                    ]),

                Section::make('Resultado de la visita')
                    ->columns(2)
                    // Solo tiene sentido rellenarlo cuando la visita ya ocurrió.
                    ->visible(fn ($get) => EnumValue::is($get('status'), VisitStatus::Realizada))
                    ->schema([
                        Select::make('interest_rating')
                            ->label('Nivel de interés')
                            ->options([
                                1 => '★☆☆☆☆ Muy bajo',
                                2 => '★★☆☆☆ Bajo',
                                3 => '★★★☆☆ Medio',
                                4 => '★★★★☆ Alto',
                                5 => '★★★★★ Muy alto',
                            ]),

                        Textarea::make('feedback')
                            ->label('Comentarios del cliente')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
