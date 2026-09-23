<?php

namespace App\Filament\Resources\Contracts\Tables;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Models\Contract;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('property.title')
                    ->label('Servicio')
                    ->searchable()
                    ->limit(35)
                    ->wrap()
                    ->description(fn (Contract $record) => $record->property?->code),

                TextColumn::make('client.first_name')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Contract $record) => $record->client?->full_name)
                    ->searchable(['contacts.first_name', 'contacts.last_name']),

                TextColumn::make('agent.name')
                    ->label('Agente')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('amount')
                    ->label('Importe')
                    ->money(fn (Contract $record) => $record->currency)
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->money('USD')),

                TextColumn::make('commission_amount')
                    ->label('Comisión')
                    ->money(fn (Contract $record) => $record->currency)
                    ->sortable()
                    ->description(fn (Contract $record) => $record->commission_percent.'%')
                    ->summarize(Sum::make()->label('Total')->money('USD')),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('signed_at')
                    ->label('Firma')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('type')->label('Tipo')->collapsible(),
                Group::make('status')->label('Estado')->collapsible(),
                Group::make('agent.name')->label('Agente')->collapsible(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(ContractStatus::class)
                    ->multiple(),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(ContractType::class)
                    ->multiple(),

                SelectFilter::make('agent_id')
                    ->label('Agente')
                    ->relationship('agent', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('this_year')
                    ->label('Firmados este año')
                    ->query(fn (Builder $query) => $query->whereYear('signed_at', now()->year)),

                Filter::make('expiring')
                    ->label('Alquileres que vencen en 60 días')
                    ->query(fn (Builder $query) => $query
                        ->where('type', ContractType::Alquiler)
                        ->where('status', ContractStatus::Activo)
                        ->whereBetween('end_date', [now(), now()->addDays(60)])),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Activar contrato')
                    ->modalDescription('El servicio cambiará de estado, el lead se marcará como ganado y se generarán las comisiones.')
                    ->visible(fn (Contract $record) => in_array($record->status, [
                        ContractStatus::Borrador,
                        ContractStatus::PendienteFirma,
                    ], true))
                    ->action(function (Contract $record) {
                        $record->update(['status' => ContractStatus::Activo]);

                        Notification::make()
                            ->title('Contrato activado')
                            ->body('Servicio marcado como '.$record->type->resultingPropertyStatus()->getLabel().'.')
                            ->success()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
