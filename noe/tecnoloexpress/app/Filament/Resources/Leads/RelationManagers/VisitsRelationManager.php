<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Filament\Resources\Visits\Schemas\VisitForm;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    protected static ?string $title = 'Visitas';

    public function form(Schema $schema): Schema
    {
        return VisitForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('property.title')
                    ->label('Servicio')
                    ->searchable()
                    ->wrap()
                    ->description(fn ($record) => $record->property?->code),

                TextColumn::make('agent.name')
                    ->label('Agente'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('interest_rating')
                    ->label('Interés')
                    ->formatStateUsing(fn (?int $state) => $state ? str_repeat('★', $state).str_repeat('☆', 5 - $state) : '—'),

                TextColumn::make('feedback')
                    ->label('Comentarios')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->headerActions([
                CreateAction::make()->label('Agendar visita'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
