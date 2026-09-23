<?php

namespace App\Filament\Resources\Contacts\RelationManagers;

use App\Enums\PropertyStatus;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OwnedPropertiesRelationManager extends RelationManager
{
    protected static string $relationship = 'ownedProperties';

    protected static ?string $title = 'Servicios en propiedad';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('code')
                    ->label('Ref.')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Servicio')
                    ->searchable()
                    ->wrap()
                    ->description(fn ($record) => collect([$record->neighborhood, $record->city])->filter()->join(', ')),

                TextColumn::make('type')->label('Tipo')->badge(),

                TextColumn::make('status')->label('Estado')->badge()->sortable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->state(fn ($record) => $record->displayPrice() ?? '—')
                    ->sortable(),

                TextColumn::make('agent.name')->label('Agente captador'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(PropertyStatus::class)
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn ($record) => \App\Filament\Resources\Properties\PropertyResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
