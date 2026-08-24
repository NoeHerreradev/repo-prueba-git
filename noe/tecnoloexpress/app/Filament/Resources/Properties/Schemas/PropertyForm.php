<?php

namespace App\Filament\Resources\Properties\Schemas;

use App\Enums\ContactType;
use App\Enums\PropertyOperation;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Support\EnumValue;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Str;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Inmueble')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('General')
                            ->icon('heroicon-o-home')
                            ->schema(self::generalTab()),

                        Tabs\Tab::make('Características')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema(self::featuresTab()),

                        Tabs\Tab::make('Ubicación')
                            ->icon('heroicon-o-map-pin')
                            ->schema(self::locationTab()),

                        Tabs\Tab::make('Fotos')
                            ->icon('heroicon-o-photo')
                            ->schema(self::photosTab()),

                        Tabs\Tab::make('Gestión')
                            ->icon('heroicon-o-briefcase')
                            ->schema(self::managementTab()),
                    ]),
            ]);
    }

    protected static function generalTab(): array
    {
        return [
            Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->live(onBlur: true)
                        // El slug solo se autogenera mientras el inmueble no esté publicado,
                        // para no romper URLs ya indexadas.
                        ->afterStateUpdated(function (?string $state, Set $set, ?Property $record) {
                            if ($record?->published) {
                                return;
                            }

                            $set('slug', Str::slug((string) $state));
                        }),

                    TextInput::make('code')
                        ->label('Referencia')
                        ->placeholder('Se genera automáticamente')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('slug')
                        ->label('URL amigable')
                        ->helperText('Se usa en la dirección pública del inmueble.')
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Select::make('type')
                        ->label('Tipo de inmueble')
                        ->options(PropertyType::class)
                        ->default(PropertyType::Apartamento)
                        ->required()
                        ->live(),

                    Select::make('operation')
                        ->label('Operación')
                        ->options(PropertyOperation::class)
                        ->default(PropertyOperation::Venta)
                        ->required()
                        ->live(),

                    Select::make('status')
                        ->label('Estado')
                        ->options(PropertyStatus::class)
                        ->default(PropertyStatus::Disponible)
                        ->required(),

                    Textarea::make('description')
                        ->label('Descripción')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),

            Section::make('Precio y comisión')
                ->columns(3)
                ->schema([
                    Select::make('currency')
                        ->label('Moneda')
                        ->options(['USD' => 'USD', 'EUR' => 'EUR', 'MXN' => 'MXN', 'COP' => 'COP', 'PEN' => 'PEN'])
                        ->default('USD')
                        ->required(),

                    TextInput::make('price')
                        ->label('Precio de venta')
                        ->numeric()
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->prefix(fn ($get) => $get('currency'))
                        ->visible(fn ($get) => EnumValue::of(PropertyOperation::class, $get('operation'))?->includesSale() ?? true)
                        ->required(fn ($get) => EnumValue::of(PropertyOperation::class, $get('operation'))?->includesSale() ?? false),

                    TextInput::make('rent_price')
                        ->label('Precio de alquiler (mensual)')
                        ->numeric()
                        ->prefix(fn ($get) => $get('currency'))
                        ->visible(fn ($get) => EnumValue::of(PropertyOperation::class, $get('operation'))?->includesRent() ?? false)
                        ->required(fn ($get) => EnumValue::of(PropertyOperation::class, $get('operation'))?->includesRent() ?? false),

                    TextInput::make('maintenance_fee')
                        ->label('Gastos comunes / expensas')
                        ->numeric()
                        ->prefix(fn ($get) => $get('currency')),

                    TextInput::make('commission_percent')
                        ->label('Comisión de la agencia')
                        ->numeric()
                        ->suffix('%')
                        ->default(3)
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),
                ]),
        ];
    }

    protected static function featuresTab(): array
    {
        return [
            Section::make()
                ->columns(3)
                ->schema([
                    TextInput::make('bedrooms')
                        ->label('Habitaciones')
                        ->numeric()
                        ->minValue(0)
                        ->visible(fn ($get) => EnumValue::of(PropertyType::class, $get('type'))?->hasRooms() ?? true),

                    TextInput::make('bathrooms')
                        ->label('Baños')
                        ->numeric()
                        ->minValue(0)
                        ->visible(fn ($get) => EnumValue::of(PropertyType::class, $get('type'))?->hasRooms() ?? true),

                    TextInput::make('parking_spaces')
                        ->label('Estacionamientos')
                        ->numeric()
                        ->minValue(0),

                    TextInput::make('area_built')
                        ->label('Área construida')
                        ->numeric()
                        ->suffix('m²'),

                    TextInput::make('area_lot')
                        ->label('Área del terreno')
                        ->numeric()
                        ->suffix('m²'),

                    TextInput::make('year_built')
                        ->label('Año de construcción')
                        ->numeric()
                        ->minValue(1800)
                        ->maxValue((int) date('Y') + 5),

                    TextInput::make('floor')
                        ->label('Piso / nivel')
                        ->maxLength(50),
                ]),

            Section::make('Amenidades')
                ->schema([
                    CheckboxList::make('amenities')
                        ->hiddenLabel()
                        ->relationship('amenities', 'name')
                        ->columns(3)
                        ->bulkToggleable()
                        ->searchable(),
                ]),
        ];
    }

    protected static function locationTab(): array
    {
        return [
            Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('address')
                        ->label('Dirección')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('neighborhood')
                        ->label('Barrio / sector')
                        ->maxLength(255),

                    TextInput::make('city')
                        ->label('Ciudad')
                        ->maxLength(255)
                        ->required(),

                    TextInput::make('state')
                        ->label('Provincia / estado')
                        ->maxLength(255),

                    TextInput::make('postal_code')
                        ->label('Código postal')
                        ->maxLength(20),

                    TextInput::make('latitude')
                        ->label('Latitud')
                        ->numeric()
                        ->step(0.0000001),

                    TextInput::make('longitude')
                        ->label('Longitud')
                        ->numeric()
                        ->step(0.0000001),

                    Toggle::make('show_exact_address')
                        ->label('Mostrar dirección exacta en el portal público')
                        ->helperText('Si está desactivado el portal solo muestra el barrio y la ciudad.')
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected static function photosTab(): array
    {
        return [
            Repeater::make('images')
                ->label('Galería')
                ->relationship()
                ->orderColumn('sort_order')
                ->reorderable()
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['caption'] ?? null)
                ->defaultItems(0)
                ->addActionLabel('Añadir foto')
                ->schema([
                    FileUpload::make('path')
                        ->label('Imagen')
                        ->image()
                        ->imageEditor()
                        ->directory('propiedades')
                        ->disk('public')
                        ->maxSize(5120)
                        ->required(),

                    Grid::make(2)->schema([
                        TextInput::make('caption')
                            ->label('Descripción')
                            ->maxLength(255),

                        Checkbox::make('is_cover')
                            ->label('Foto de portada'),
                    ]),
                ]),
        ];
    }

    protected static function managementTab(): array
    {
        return [
            Section::make()
                ->columns(2)
                ->schema([
                    Select::make('owner_id')
                        ->label('Propietario')
                        ->relationship(
                            'owner',
                            'first_name',
                            fn ($query) => $query->where('type', ContactType::Propietario),
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                        ->searchable(['first_name', 'last_name', 'email'])
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('first_name')->label('Nombre')->required(),
                            TextInput::make('last_name')->label('Apellido'),
                            TextInput::make('email')->label('Email')->email(),
                            TextInput::make('phone')->label('Teléfono')->tel(),
                        ])
                        ->createOptionUsing(fn (array $data) => \App\Models\Contact::create([
                            ...$data,
                            'type' => ContactType::Propietario,
                        ])->getKey()),

                    Select::make('agent_id')
                        ->label('Agente captador')
                        ->relationship('agent', 'name', fn ($query) => $query->where('active', true))
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),

                    DatePicker::make('captured_at')
                        ->label('Fecha de captación')
                        ->default(now()),

                    Toggle::make('exclusive')
                        ->label('Exclusiva')
                        ->helperText('La agencia tiene la venta en exclusiva.'),

                    Toggle::make('published')
                        ->label('Publicado en el portal')
                        ->helperText('Debe tener al menos una foto para verse bien.'),

                    Toggle::make('featured')
                        ->label('Destacado en la portada'),
                ]),
        ];
    }
}
