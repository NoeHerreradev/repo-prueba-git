<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Enums\TaskPriority;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tareas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Tarea')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                DateTimePicker::make('due_at')
                    ->label('Vence')
                    ->default(now()->addDay()),

                Select::make('priority')
                    ->label('Prioridad')
                    ->options(TaskPriority::class)
                    ->default(TaskPriority::Media)
                    ->required(),

                Select::make('user_id')
                    ->label('Responsable')
                    ->options(fn () => \App\Models\User::where('active', true)->pluck('name', 'id'))
                    ->default(fn () => auth()->id())
                    ->searchable(),

                Textarea::make('description')
                    ->label('Detalle')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                IconColumn::make('completed_at')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->state(fn (Task $record) => $record->isCompleted()),

                TextColumn::make('title')
                    ->label('Tarea')
                    ->searchable()
                    ->wrap()
                    ->description(fn (Task $record) => $record->description),

                TextColumn::make('due_at')
                    ->label('Vence')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color(fn (Task $record) => $record->isOverdue() ? 'danger' : null)
                    ->badge(fn (Task $record) => $record->isOverdue()),

                TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Responsable')
                    ->placeholder('Sin asignar'),
            ])
            ->defaultSort('due_at')
            ->filters([
                Filter::make('pending')
                    ->label('Solo pendientes')
                    ->query(fn (Builder $query) => $query->pending())
                    ->default(),
            ])
            ->headerActions([
                CreateAction::make()->label('Nueva tarea'),
            ])
            ->recordActions([
                Action::make('toggle')
                    ->label(fn (Task $record) => $record->isCompleted() ? 'Reabrir' : 'Completar')
                    ->icon(fn (Task $record) => $record->isCompleted() ? 'heroicon-m-arrow-uturn-left' : 'heroicon-m-check')
                    ->color(fn (Task $record) => $record->isCompleted() ? 'gray' : 'success')
                    ->action(fn (Task $record) => $record->update([
                        'completed_at' => $record->isCompleted() ? null : now(),
                    ])),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
