<?php

namespace App\Filament\Resources\Properties\Tables;

use App\Enums\PropertyOperation;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')
                    ->label('')
                    ->disk('public')
                    ->height(48)
                    ->width(64)
                    ->extraImgAttributes(['class' => 'rounded object-cover'])
                    ->defaultImageUrl(asset('images/placeholder.svg'))
                    ->state(fn ($record) => $record->coverImage()?->path),

                TextColumn::make('code')
                    ->label('Ref.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),

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
                    ->state(fn ($record) => $record->displayPrice() ?? '—')
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total cartera')->money('USD')),

                TextColumn::make('bedrooms')
                    ->label('Hab.')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('area_built')
                    ->label('m²')
                    ->numeric(decimalPlaces: 0)
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('agent.name')
                    ->label('Agente')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('owner.first_name')
                    ->label('Propietario')
                    ->formatStateUsing(fn ($record) => $record->owner?->full_name)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('published')
                    ->label('Publicado')
                    ->toggleable(),

                TextColumn::make('views_count')
                    ->label('Visitas web')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('captured_at')
                    ->label('Captado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn ($record) => $record->daysOnMarket() !== null ? "{$record->daysOnMarket()} días" : null)
                    ->toggleable(isToggledHiddenByDefault: true),
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

                SelectFilter::make('operation')
                    ->label('Operación')
                    ->options(PropertyOperation::class),

                SelectFilter::make('agent_id')
                    ->label('Agente')
                    ->relationship('agent', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('published')
                    ->label('Publicado en el portal'),

                TernaryFilter::make('exclusive')
                    ->label('En exclusiva'),

                Filter::make('price_range')
                    ->label('Rango de precio')
                    ->schema([
                        TextInput::make('price_from')->label('Desde')->numeric(),
                        TextInput::make('price_to')->label('Hasta')->numeric(),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['price_from'] ?? null, fn (Builder $q, $v) => $q->where('price', '>=', $v))
                        ->when($data['price_to'] ?? null, fn (Builder $q, $v) => $q->where('price', '<=', $v))),

                Filter::make('city')
                    ->label('Ciudad')
                    ->schema([
                        Select::make('city')
                            ->label('Ciudad')
                            ->options(fn () => \App\Models\Property::query()
                                ->whereNotNull('city')
                                ->distinct()
                                ->orderBy('city')
                                ->pluck('city', 'city')
                                ->all())
                            ->searchable(),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['city'] ?? null, fn (Builder $q, $v) => $q->where('city', $v))),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('assignAgent')
                    ->label('Asignar agente')
                    ->icon('heroicon-m-user-plus')
                    ->schema([
                        Select::make('agent_id')
                            ->label('Agente asignado')
                            ->options(fn () => \App\Models\User::where('active', true)->pluck('name', 'id'))
                            ->default(fn ($record) => $record->agent_id)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['agent_id' => $data['agent_id']]);

                        Notification::make()
                            ->title('Agente asignado correctamente')
                            ->success()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('reassign')
                        ->label('Asignar / Reasignar agente')
                        ->icon('heroicon-m-user-plus')
                        ->schema([
                            Select::make('agent_id')
                                ->label('Agente')
                                ->options(fn () => \App\Models\User::where('active', true)->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update(['agent_id' => $data['agent_id']]);

                            Notification::make()
                                ->title($records->count().' servicios asignados')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
