<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Enums\ContractType;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class ContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('number')
                            ->label('Contrato')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->copyable()
                            ->columnSpan(2),

                        TextEntry::make('type')->label('Tipo')->badge(),
                        TextEntry::make('status')->label('Estado')->badge(),

                        TextEntry::make('property.title')
                            ->label('Servicio')
                            ->url(fn ($record) => $record->property
                                ? \App\Filament\Resources\Properties\PropertyResource::getUrl('view', ['record' => $record->property])
                                : null)
                            ->columnSpan(2),

                        TextEntry::make('client.first_name')
                            ->label(fn ($record) => $record->type === ContractType::Alquiler ? 'Arrendatario' : 'Comprador')
                            ->state(fn ($record) => $record->client?->full_name)
                            ->placeholder('—'),

                        TextEntry::make('owner.first_name')
                            ->label('Propietario')
                            ->state(fn ($record) => $record->owner?->full_name)
                            ->placeholder('—'),
                    ]),

                Grid::make(2)->columnSpanFull()->schema([
                    Section::make('Importes')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('amount')
                                ->label('Importe')
                                ->money(fn ($record) => $record->currency)
                                ->size(TextSize::Large)
                                ->color('primary')
                                ->visible(fn ($record) => $record->type !== ContractType::Alquiler),

                            TextEntry::make('monthly_rent')
                                ->label('Renta mensual')
                                ->money(fn ($record) => $record->currency)
                                ->size(TextSize::Large)
                                ->color('primary')
                                ->visible(fn ($record) => $record->type === ContractType::Alquiler),

                            TextEntry::make('deposit')
                                ->label('Depósito')
                                ->money(fn ($record) => $record->currency)
                                ->placeholder('—'),

                            TextEntry::make('commission_amount')
                                ->label('Comisión de la agencia')
                                ->money(fn ($record) => $record->currency)
                                ->helperText(fn ($record) => $record->commission_percent.'% de la operación'),

                            TextEntry::make('agent.name')->label('Agente que cierra')->placeholder('—'),
                        ]),

                    Section::make('Fechas')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('signed_at')->label('Firma')->date('d/m/Y')->placeholder('—'),
                            TextEntry::make('start_date')->label('Inicio')->date('d/m/Y')->placeholder('—'),
                            TextEntry::make('end_date')->label('Fin')->date('d/m/Y')->placeholder('—'),
                            TextEntry::make('created_at')->label('Creado')->date('d/m/Y'),
                            TextEntry::make('notes')->label('Observaciones')->placeholder('Sin observaciones')->columnSpanFull(),
                        ]),
                ]),

                Section::make('Reparto de comisiones')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('commissions')
                            ->hiddenLabel()
                            ->contained(false)
                            ->placeholder('Aún no se han generado comisiones')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('user.name')->label('Agente'),
                                    TextEntry::make('role')->label('Rol')->badge(),
                                    TextEntry::make('amount')->label('Importe')->money('USD'),
                                    TextEntry::make('status')->label('Estado')->badge(),
                                ]),
                            ]),
                    ]),
            ]);
    }
}
