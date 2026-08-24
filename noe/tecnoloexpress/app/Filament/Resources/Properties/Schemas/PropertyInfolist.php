<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class PropertyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('code')->label('Referencia')->badge()->copyable(),
                        TextEntry::make('status')->label('Estado')->badge(),
                        TextEntry::make('operation')->label('Operación')->badge(),
                        TextEntry::make('type')->label('Tipo')->badge(),

                        TextEntry::make('title')
                            ->label('Título')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('price')
                            ->label('Precio')
                            ->state(fn ($record) => $record->displayPrice() ?? '—')
                            ->size(TextSize::Large)
                            ->color('primary')
                            ->columnSpan(2),

                        TextEntry::make('commission_percent')
                            ->label('Comisión estimada')
                            ->state(fn ($record) => number_format($record->estimatedCommission(), 2).' '.$record->currency
                                ." ({$record->commission_percent}%)")
                            ->columnSpan(2),

                        TextEntry::make('description')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->placeholder('Sin descripción'),
                    ]),

                Section::make('Galería')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('images')
                            ->hiddenLabel()
                            ->contained(false)
                            ->grid(4)
                            ->schema([
                                ImageEntry::make('path')
                                    ->hiddenLabel()
                                    ->disk('public')
                                    ->height(150)
                                    ->width('100%')
                                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->images()->exists()),

                Grid::make(2)->columnSpanFull()->schema([
                    Section::make('Características')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('bedrooms')->label('Habitaciones')->placeholder('—'),
                            TextEntry::make('bathrooms')->label('Baños')->placeholder('—'),
                            TextEntry::make('parking_spaces')->label('Estacionamientos')->placeholder('—'),
                            TextEntry::make('area_built')->label('Área construida')->suffix(' m²')->placeholder('—'),
                            TextEntry::make('area_lot')->label('Área del terreno')->suffix(' m²')->placeholder('—'),
                            TextEntry::make('year_built')->label('Año')->placeholder('—'),
                            TextEntry::make('amenities.name')
                                ->label('Amenidades')
                                ->badge()
                                ->placeholder('Sin amenidades')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Ubicación y gestión')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('address')->label('Dirección')->placeholder('—')->columnSpanFull(),
                            TextEntry::make('neighborhood')->label('Barrio')->placeholder('—'),
                            TextEntry::make('city')->label('Ciudad')->placeholder('—'),
                            TextEntry::make('owner.first_name')
                                ->label('Propietario')
                                ->state(fn ($record) => $record->owner?->full_name)
                                ->placeholder('—'),
                            TextEntry::make('agent.name')->label('Agente captador')->placeholder('—'),
                            TextEntry::make('captured_at')
                                ->label('Captado')
                                ->date('d/m/Y')
                                ->placeholder('—')
                                ->helperText(fn ($record) => $record->daysOnMarket() !== null
                                    ? "{$record->daysOnMarket()} días en cartera"
                                    : null),
                            IconEntry::make('exclusive')->label('Exclusiva')->boolean(),
                            IconEntry::make('published')->label('Publicado')->boolean(),
                            TextEntry::make('views_count')->label('Vistas en el portal'),
                        ]),
                ]),
            ]);
    }
}
