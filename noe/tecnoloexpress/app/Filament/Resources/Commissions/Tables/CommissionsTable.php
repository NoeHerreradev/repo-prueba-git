<?php

namespace App\Filament\Resources\Commissions\Tables;

use App\Enums\CommissionRole;
use App\Enums\CommissionStatus;
use App\Models\Commission;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CommissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract.number')
                    ->label('Contrato')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Commission $record) => $record->contract
                        ? \App\Filament\Resources\Contracts\ContractResource::getUrl('view', ['record' => $record->contract])
                        : null),

                TextColumn::make('contract.property.title')
                    ->label('Inmueble')
                    ->limit(35)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Agente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('percent')
                    ->label('Reparto')
                    ->suffix('%')
                    ->alignCenter(),

                TextColumn::make('amount')
                    ->label('Importe')
                    ->money('USD')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->money('USD')),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Pagada')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Generada')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('user.name')->label('Agente')->collapsible(),
                Group::make('status')->label('Estado')->collapsible(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(CommissionStatus::class)
                    ->multiple(),

                SelectFilter::make('role')
                    ->label('Rol')
                    ->options(CommissionRole::class),

                SelectFilter::make('user_id')
                    ->label('Agente')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('unpaid')
                    ->label('Solo sin pagar')
                    ->query(fn (Builder $query) => $query->where('status', '!=', CommissionStatus::Pagada)),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check')
                    ->color('warning')
                    ->visible(fn (Commission $record) => $record->status === CommissionStatus::Pendiente)
                    ->action(fn (Commission $record) => $record->update(['status' => CommissionStatus::Aprobada])),

                Action::make('pay')
                    ->label('Marcar pagada')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->visible(fn (Commission $record) => $record->status === CommissionStatus::Aprobada)
                    ->schema([
                        DatePicker::make('paid_at')
                            ->label('Fecha de pago')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(fn (Commission $record, array $data) => $record->update([
                        'status' => CommissionStatus::Pagada,
                        'paid_at' => $data['paid_at'],
                    ])),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('pay_all')
                        ->label('Liquidar seleccionadas')
                        ->icon('heroicon-m-banknotes')
                        ->color('success')
                        ->requiresConfirmation()
                        ->schema([
                            DatePicker::make('paid_at')
                                ->label('Fecha de pago')
                                ->default(now())
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update([
                                'status' => CommissionStatus::Pagada,
                                'paid_at' => $data['paid_at'],
                            ]);

                            Notification::make()
                                ->title($records->count().' comisiones liquidadas')
                                ->body('Total: $'.number_format((float) $records->sum('amount'), 2))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
