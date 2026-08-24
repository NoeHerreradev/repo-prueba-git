<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\CommissionStatus;
use App\Enums\UserRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=6366f1&color=fff'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->email),

                TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('properties_count')
                    ->label('Inmuebles')
                    ->counts('properties')
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('leads_count')
                    ->label('Leads')
                    ->counts('leads')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('contracts_count')
                    ->label('Contratos')
                    ->counts('contracts')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                TextColumn::make('commissions_sum_amount')
                    ->label('Comisiones pagadas')
                    ->sum(['commissions' => fn ($query) => $query->where('status', CommissionStatus::Pagada)], 'amount')
                    ->money('USD')
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label('Activo'),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options(UserRole::class)
                    ->multiple(),

                TernaryFilter::make('active')
                    ->label('Activo'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
