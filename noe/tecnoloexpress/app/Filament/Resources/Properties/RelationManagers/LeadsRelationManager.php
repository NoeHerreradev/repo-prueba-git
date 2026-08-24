<?php

namespace App\Filament\Resources\Properties\RelationManagers;

use App\Enums\LeadStage;
use App\Filament\Resources\Leads\Schemas\LeadForm;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadsRelationManager extends RelationManager
{
    protected static string $relationship = 'leads';

    protected static ?string $title = 'Interesados';

    public function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->heading('Interesados en este inmueble')
            ->columns([
                TextColumn::make('contact.first_name')
                    ->label('Contacto')
                    ->formatStateUsing(fn ($record) => $record->contact?->full_name)
                    ->searchable()
                    ->description(fn ($record) => $record->contact?->phone),

                TextColumn::make('stage')
                    ->label('Etapa')
                    ->badge()
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Origen')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('expected_value')
                    ->label('Valor estimado')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('agent.name')
                    ->label('Agente'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('stage')
                    ->label('Etapa')
                    ->options(LeadStage::class)
                    ->multiple(),
            ])
            ->headerActions([
                CreateAction::make()->label('Nuevo interesado'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
