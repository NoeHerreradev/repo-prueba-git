<?php

namespace App\Filament\Resources\Properties\RelationManagers;

use App\Enums\VisitStatus;
use App\Filament\Resources\Visits\Schemas\VisitForm;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
            ->heading('Visitas al servicio')
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('contact.first_name')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($record) => $record->contact?->full_name)
                    ->searchable(),

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
                    ->wrap(),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(VisitStatus::class)
                    ->multiple(),
            ])
            ->headerActions([
                CreateAction::make()->label('Agendar visita'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
