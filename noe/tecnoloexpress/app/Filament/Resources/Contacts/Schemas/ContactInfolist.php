<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class ContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('first_name')
                            ->label('Contacto')
                            ->state(fn ($record) => $record->full_name)
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpan(2),

                        TextEntry::make('type')->label('Tipo')->badge(),
                        TextEntry::make('email')->label('Email')->copyable()->placeholder('—'),
                        TextEntry::make('phone')->label('Teléfono')->copyable()->placeholder('—'),
                        TextEntry::make('whatsapp')
                            ->label('WhatsApp')
                            ->placeholder('—')
                            ->url(fn ($record) => $record->whatsapp
                                ? 'https://wa.me/'.preg_replace('/\D/', '', $record->whatsapp)
                                : null)
                            ->openUrlInNewTab(),
                        TextEntry::make('document_number')->label('Documento')->placeholder('—'),
                        TextEntry::make('city')->label('Ciudad')->placeholder('—'),
                        TextEntry::make('source')->label('Origen')->badge()->color('gray'),
                    ]),

                Grid::make(2)->columnSpanFull()->schema([
                    Section::make('Preferencias de búsqueda')
                        ->columns(2)
                        ->visible(fn ($record) => $record->type->isBuyer())
                        ->schema([
                            TextEntry::make('pref_operation')->label('Operación')->badge()->placeholder('—'),
                            TextEntry::make('pref_property_type')->label('Tipo')->badge()->placeholder('—'),
                            TextEntry::make('pref_city')->label('Zona')->placeholder('—'),
                            TextEntry::make('pref_bedrooms_min')->label('Habitaciones mín.')->placeholder('—'),
                            TextEntry::make('budget_min')->label('Presupuesto mín.')->money('USD')->placeholder('—'),
                            TextEntry::make('budget_max')->label('Presupuesto máx.')->money('USD')->placeholder('—'),
                            TextEntry::make('matches')
                                ->label('Inmuebles que encajan')
                                ->state(fn ($record) => $record->matchingProperties()->count().' disponibles')
                                ->badge()
                                ->color('success')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Seguimiento')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('agent.name')->label('Agente asignado')->placeholder('Sin asignar'),
                            TextEntry::make('created_at')->label('Alta')->date('d/m/Y'),
                            TextEntry::make('notes')
                                ->label('Notas')
                                ->placeholder('Sin notas')
                                ->columnSpanFull(),
                        ]),
                ]),
            ]);
    }
}
