<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\ContactType;
use App\Enums\LeadSource;
use App\Enums\LeadStage;
use App\Models\Contact;
use App\Models\Property;
use App\Support\EnumValue;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Oportunidad')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->placeholder('Ej. Interesado en apartamento en Cumbayá')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('contact_id')
                            ->label('Contacto')
                            ->relationship('contact', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable(['first_name', 'last_name', 'email', 'phone'])
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('first_name')->label('Nombre')->required(),
                                TextInput::make('last_name')->label('Apellido'),
                                TextInput::make('email')->label('Email')->email(),
                                TextInput::make('phone')->label('Teléfono')->tel(),
                                Select::make('type')
                                    ->label('Tipo')
                                    ->options(ContactType::class)
                                    ->default(ContactType::Comprador)
                                    ->required(),
                            ]),

                        Select::make('property_id')
                            ->label('Inmueble de interés')
                            ->relationship('property', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} — {$record->title}")
                            ->searchable(['title', 'code', 'city'])
                            ->preload()
                            ->placeholder('Consulta general (sin inmueble)')
                            // Al elegir inmueble se propone su precio como valor esperado.
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                if (! $state) {
                                    return;
                                }

                                $property = Property::find($state);
                                $set('expected_value', $property?->price);
                            }),

                        Select::make('source')
                            ->label('Origen')
                            ->options(LeadSource::class)
                            ->default(LeadSource::Web)
                            ->required(),

                        Select::make('assigned_agent_id')
                            ->label('Agente asignado')
                            ->relationship('agent', 'name', fn ($query) => $query->where('active', true))
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id()),
                    ]),

                Section::make('Estado del embudo')
                    ->columns(3)
                    ->schema([
                        Select::make('stage')
                            ->label('Etapa')
                            ->options(LeadStage::class)
                            ->default(LeadStage::Nuevo)
                            ->required()
                            ->live()
                            // Cada etapa lleva una probabilidad de cierre orientativa.
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('probability', EnumValue::of(LeadStage::class, $state)?->defaultProbability());
                            }),

                        TextInput::make('probability')
                            ->label('Probabilidad de cierre')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(10),

                        TextInput::make('expected_value')
                            ->label('Valor estimado')
                            ->numeric()
                            ->prefix('$'),

                        DatePicker::make('expected_close_date')
                            ->label('Cierre estimado'),

                        TextInput::make('lost_reason')
                            ->label('Motivo de pérdida')
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->visible(fn ($get) => EnumValue::is($get('stage'), LeadStage::Perdido))
                            ->required(fn ($get) => EnumValue::is($get('stage'), LeadStage::Perdido)),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
