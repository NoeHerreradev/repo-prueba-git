<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Enums\ActivityType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Actividad';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo')
                    ->options(ActivityType::class)
                    ->default(ActivityType::Llamada)
                    ->required(),

                DateTimePicker::make('occurred_at')
                    ->label('Fecha y hora')
                    ->default(now())
                    ->required(),

                TextInput::make('subject')
                    ->label('Asunto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Detalle')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('notes')
                    ->label('Detalle')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->placeholder('Sistema'),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(ActivityType::class)
                    ->multiple(),
            ])
            ->headerActions([
                CreateAction::make()->label('Registrar actividad'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
