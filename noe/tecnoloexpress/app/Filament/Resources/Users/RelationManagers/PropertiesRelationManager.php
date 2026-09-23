<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\PropertyOperation;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Resources\Properties\Schemas\PropertyForm;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropertiesRelationManager extends RelationManager
{
    protected static string $relationship = 'properties';

    protected static ?string $title = 'Servicios asignados';

    public function form(Schema $schema): Schema
    {
        return PropertyForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->heading('Servicios asignados a este usuario')
            ->columns([
                ImageColumn::make('cover')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->width(50)
                    ->extraImgAttributes(['class' => 'rounded object-cover'])
                    ->defaultImageUrl(asset('images/placeholder.svg'))
                    ->state(fn ($record) => $record->coverImage()?->path),

                TextColumn::make('code')
                    ->label('Ref.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn ($record) => collect([$record->neighborhood, $record->city])->filter()->join(', ')),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('operation')
                    ->label('Operación')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->sortable()
                    ->state(fn ($record) => $record->displayPrice() ?? '—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(PropertyStatus::class)
                    ->multiple(),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(PropertyType::class)
                    ->multiple(),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Asignar servicio existente')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['title', 'code', 'city']),
                CreateAction::make()
                    ->label('Crear y asignar servicio'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => PropertyResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn ($record) => PropertyResource::getUrl('edit', ['record' => $record])),
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
