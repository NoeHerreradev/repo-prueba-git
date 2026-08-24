<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Models\Property;
use App\Support\EnumValue;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contrato')
                    ->columns(3)
                    ->schema([
                        TextInput::make('number')
                            ->label('Número')
                            ->placeholder('Se genera automáticamente')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('type')
                            ->label('Tipo')
                            ->options(ContractType::class)
                            ->default(ContractType::Venta)
                            ->required()
                            ->live(),

                        Select::make('status')
                            ->label('Estado')
                            ->options(ContractStatus::class)
                            ->default(ContractStatus::Borrador)
                            ->required()
                            ->helperText('Al pasar a «Activo» se actualiza el inmueble y se generan las comisiones.'),

                        Select::make('property_id')
                            ->label('Inmueble')
                            ->relationship('property', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} — {$record->title}")
                            ->searchable(['title', 'code', 'city'])
                            ->preload()
                            ->required()
                            ->live()
                            // El inmueble aporta precio, propietario, agente captador y comisión pactada.
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                                if (! $state) {
                                    return;
                                }

                                $property = Property::find($state);

                                if (! $property) {
                                    return;
                                }

                                $set('owner_contact_id', $property->owner_id);
                                $set('commission_percent', $property->commission_percent);
                                $set('currency', $property->currency);

                                if (EnumValue::of(ContractType::class, $get('type')) === ContractType::Alquiler) {
                                    $set('monthly_rent', $property->rent_price);
                                } else {
                                    $set('amount', $property->price);
                                }

                                if (blank($get('agent_id'))) {
                                    $set('agent_id', $property->agent_id);
                                }
                            }),

                        Select::make('lead_id')
                            ->label('Lead de origen')
                            ->relationship('lead', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->contact?->full_name.' — '.($record->property?->code ?? 'general'))
                            ->searchable()
                            ->preload()
                            ->helperText('Al activar el contrato el lead se marca como ganado.'),

                        Select::make('agent_id')
                            ->label('Agente que cierra')
                            ->relationship('agent', 'name', fn ($query) => $query->where('active', true))
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id()),
                    ]),

                Section::make('Partes')
                    ->columns(2)
                    ->schema([
                        Select::make('client_contact_id')
                            ->label(fn (Get $get) => EnumValue::of(ContractType::class, $get('type')) === ContractType::Alquiler
                                ? 'Arrendatario'
                                : 'Comprador')
                            ->relationship('client', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->required(),

                        Select::make('owner_contact_id')
                            ->label('Propietario / vendedor')
                            ->relationship('owner', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload(),
                    ]),

                Section::make('Importes')
                    ->columns(3)
                    ->schema([
                        Select::make('currency')
                            ->label('Moneda')
                            ->options(['USD' => 'USD', 'EUR' => 'EUR', 'MXN' => 'MXN', 'COP' => 'COP', 'PEN' => 'PEN'])
                            ->default('USD')
                            ->required(),

                        TextInput::make('amount')
                            ->label('Importe de la operación')
                            ->numeric()
                            ->prefix(fn (Get $get) => $get('currency'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::syncCommission($set, $get))
                            ->visible(fn (Get $get) => EnumValue::of(ContractType::class, $get('type')) !== ContractType::Alquiler)
                            ->required(fn (Get $get) => EnumValue::of(ContractType::class, $get('type')) !== ContractType::Alquiler),

                        TextInput::make('monthly_rent')
                            ->label('Renta mensual')
                            ->numeric()
                            ->prefix(fn (Get $get) => $get('currency'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::syncCommission($set, $get))
                            ->visible(fn (Get $get) => EnumValue::of(ContractType::class, $get('type')) === ContractType::Alquiler)
                            ->required(fn (Get $get) => EnumValue::of(ContractType::class, $get('type')) === ContractType::Alquiler),

                        TextInput::make('deposit')
                            ->label('Depósito / garantía')
                            ->numeric()
                            ->prefix(fn (Get $get) => $get('currency')),

                        TextInput::make('commission_percent')
                            ->label('Comisión')
                            ->numeric()
                            ->suffix('%')
                            ->default(3)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::syncCommission($set, $get)),

                        TextInput::make('commission_amount')
                            ->label('Comisión calculada')
                            ->numeric()
                            ->prefix(fn (Get $get) => $get('currency'))
                            ->disabled()
                            ->dehydrated()
                            ->helperText(fn (Get $get) => EnumValue::of(ContractType::class, $get('type')) === ContractType::Alquiler
                                ? 'Sobre la renta anual. Se reparte 50/50 entre captador y agente que cierra.'
                                : 'Se reparte 50/50 entre captador y agente que cierra.'),
                    ]),

                Section::make('Fechas')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('signed_at')
                            ->label('Fecha de firma')
                            ->default(now()),

                        DatePicker::make('start_date')
                            ->label('Inicio de vigencia'),

                        DatePicker::make('end_date')
                            ->label('Fin de vigencia')
                            ->visible(fn (Get $get) => EnumValue::of(ContractType::class, $get('type')) === ContractType::Alquiler)
                            ->after('start_date'),

                        Textarea::make('notes')
                            ->label('Observaciones')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Mantiene la comisión calculada en sincronía con la base y el porcentaje.
     * En alquiler la base es la renta anual, igual que en Contract::commissionBase().
     */
    protected static function syncCommission(Set $set, Get $get): void
    {
        $base = EnumValue::of(ContractType::class, $get('type')) === ContractType::Alquiler
            ? (float) $get('monthly_rent') * 12
            : (float) $get('amount');

        $set('commission_amount', round($base * ((float) $get('commission_percent') / 100), 2));
    }
}
