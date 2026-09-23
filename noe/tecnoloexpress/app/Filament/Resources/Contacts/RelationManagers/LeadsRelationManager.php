<?php

namespace App\Filament\Resources\Contacts\RelationManagers;

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

    protected static ?string $title = 'Oportunidades';

    public function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('property.title')
                    ->label('Servicio')
                    ->placeholder('Consulta general')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('stage')
                    ->label('Etapa')
                    ->badge()
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Origen')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('expected_value')
                    ->label('Valor')
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
                CreateAction::make()->label('Nueva oportunidad'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
