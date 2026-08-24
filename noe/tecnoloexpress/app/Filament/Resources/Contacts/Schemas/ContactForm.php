<?php

namespace App\Filament\Resources\Contacts\Schemas;

use App\Enums\ContactType;
use App\Enums\LeadSource;
use App\Enums\PropertyOperation;
use App\Enums\PropertyType;
use App\Support\EnumValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos personales')
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Tipo de contacto')
                            ->options(ContactType::class)
                            ->default(ContactType::Comprador)
                            ->required()
                            ->live(),

                        Select::make('source')
                            ->label('Origen')
                            ->options(LeadSource::class)
                            ->default(LeadSource::Otro)
                            ->required(),

                        TextInput::make('first_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->label('Apellido')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(50),

                        Select::make('document_type')
                            ->label('Tipo de documento')
                            ->options([
                                'cedula' => 'Cédula',
                                'ruc' => 'RUC / NIT',
                                'pasaporte' => 'Pasaporte',
                                'dni' => 'DNI',
                            ]),

                        TextInput::make('document_number')
                            ->label('Número de documento')
                            ->maxLength(50),
                    ]),

                Section::make('Dirección')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255),

                        TextInput::make('city')
                            ->label('Ciudad')
                            ->maxLength(255),
                    ]),

                Section::make('Preferencias de búsqueda')
                    ->description('Se usan para cruzar el contacto con los inmuebles disponibles.')
                    ->columns(3)
                    ->collapsible()
                    // Un propietario no busca inmueble, así que no tiene preferencias.
                    ->visible(fn ($get) => EnumValue::of(ContactType::class, $get('type'))?->isBuyer() ?? true)
                    ->schema([
                        Select::make('pref_operation')
                            ->label('Operación buscada')
                            ->options(PropertyOperation::class),

                        Select::make('pref_property_type')
                            ->label('Tipo de inmueble')
                            ->options(PropertyType::class),

                        TextInput::make('pref_city')
                            ->label('Ciudad / zona')
                            ->maxLength(255),

                        TextInput::make('budget_min')
                            ->label('Presupuesto mínimo')
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('budget_max')
                            ->label('Presupuesto máximo')
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('pref_bedrooms_min')
                            ->label('Habitaciones mínimas')
                            ->numeric()
                            ->minValue(0),
                    ]),

                Section::make('Seguimiento')
                    ->columns(2)
                    ->schema([
                        Select::make('assigned_agent_id')
                            ->label('Agente asignado')
                            ->relationship('agent', 'name', fn ($query) => $query->where('active', true))
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id()),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
