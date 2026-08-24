<?php

namespace App\Filament\Resources\Contacts\Tables;

use App\Enums\ContactType;
use App\Enums\LeadSource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Nombre')
                    ->formatStateUsing(fn ($record) => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->email),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable()
                    ->url(fn ($record) => $record->whatsapp
                        ? 'https://wa.me/'.preg_replace('/\D/', '', $record->whatsapp)
                        : null)
                    ->openUrlInNewTab(),

                TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('source')
                    ->label('Origen')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('leads_count')
                    ->label('Leads')
                    ->counts('leads')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('owned_properties_count')
                    ->label('Inmuebles')
                    ->counts('ownedProperties')
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->toggleable(),

                TextColumn::make('budget_max')
                    ->label('Presupuesto')
                    ->formatStateUsing(fn ($record) => $record->budget_max
                        ? 'hasta $'.number_format((float) $record->budget_max, 0, ',', '.')
                        : '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('agent.name')
                    ->label('Agente')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Alta')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(ContactType::class)
                    ->multiple(),

                SelectFilter::make('source')
                    ->label('Origen')
                    ->options(LeadSource::class)
                    ->multiple(),

                SelectFilter::make('assigned_agent_id')
                    ->label('Agente')
                    ->relationship('agent', 'name')
                    ->searchable()
                    ->preload(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
