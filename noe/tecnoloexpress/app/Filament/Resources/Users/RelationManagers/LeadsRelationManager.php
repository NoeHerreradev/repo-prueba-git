<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\LeadStage;
use App\Enums\LeadSource;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Leads\Schemas\LeadForm;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadsRelationManager extends RelationManager
{
    protected static string $relationship = 'leads';

    protected static ?string $title = 'Leads asignados';

    public function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->heading('Leads asignados a este usuario')
            ->columns([
                TextColumn::make('contact.first_name')
                    ->label('Contacto')
                    ->formatStateUsing(fn ($record) => $record->contact?->full_name)
                    ->searchable()
                    ->description(fn ($record) => $record->contact?->phone),

                TextColumn::make('property.title')
                    ->label('Servicio de interés')
                    ->placeholder('Consulta general')
                    ->limit(35)
                    ->description(fn ($record) => $record->property?->code),

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

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('stage')
                    ->label('Etapa')
                    ->options(LeadStage::class)
                    ->multiple(),

                SelectFilter::make('source')
                    ->label('Origen')
                    ->options(LeadSource::class),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Asignar lead existente')
                    ->preloadRecordSelect(),
                CreateAction::make()
                    ->label('Crear y asignar lead'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => LeadResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn ($record) => LeadResource::getUrl('edit', ['record' => $record])),
                DissociateAction::make()
                    ->label('Desasignar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Desasignar seleccionados'),
                ]),
            ]);
    }
}
