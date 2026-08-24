<?php

namespace App\Filament\Resources\Commissions\Schemas;

use App\Enums\CommissionRole;
use App\Enums\CommissionStatus;
use App\Support\EnumValue;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('contract_id')
                            ->label('Contrato')
                            ->relationship('contract', 'number')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('user_id')
                            ->label('Agente')
                            ->relationship('user', 'name', fn ($query) => $query->where('active', true))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('role')
                            ->label('Rol en la operación')
                            ->options(CommissionRole::class)
                            ->default(CommissionRole::Vendedor)
                            ->required(),

                        TextInput::make('percent')
                            ->label('Reparto')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),

                        TextInput::make('amount')
                            ->label('Importe')
                            ->numeric()
                            ->prefix('$')
                            ->required(),

                        Select::make('status')
                            ->label('Estado')
                            ->options(CommissionStatus::class)
                            ->default(CommissionStatus::Pendiente)
                            ->required()
                            ->live(),

                        DatePicker::make('paid_at')
                            ->label('Fecha de pago')
                            ->visible(fn ($get) => EnumValue::is($get('status'), CommissionStatus::Pagada)),
                    ]),
            ]);
    }
}
