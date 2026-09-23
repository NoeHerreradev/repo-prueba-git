<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del usuario')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                            // Al editar, dejar el campo vacío conserva la contraseña actual.
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->required(fn (string $operation) => $operation === 'create')
                            ->helperText(fn (string $operation) => $operation === 'edit'
                                ? 'Déjalo vacío para no cambiarla.'
                                : null),

                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(50),

                        FileUpload::make('avatar_path')
                            ->label('Foto')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatares'),
                    ]),

                Section::make('Permisos y comisión')
                    ->columns(3)
                    ->schema([
                        Select::make('role')
                            ->label('Rol')
                            ->options(UserRole::class)
                            ->default(UserRole::Agente)
                            ->required()
                            ->helperText('Los administradores tienen acceso total a todos los módulos.'),

                        TextInput::make('commission_percent')
                            ->label('Comisión por defecto')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100),

                        Toggle::make('active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Si se desactiva no podrá entrar al CRM.'),
                    ]),

                Section::make('Módulos asignados del CRM')
                    ->description('Selecciona a qué módulos del sistema tendrá acceso este usuario. Si el rol es Administrador, tiene acceso total a todos.')
                    ->schema([
                        CheckboxList::make('allowed_modules')
                            ->label('Módulos permitidos')
                            ->options(User::MODULES)
                            ->columns(2)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->helperText('Marca los módulos que podrá ver y gestionar este usuario en el panel.'),
                    ]),
            ]);
    }
}
