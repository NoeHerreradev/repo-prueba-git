<?php

namespace App\Filament\Pages;

use App\Models\CustomForm;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Módulo de Ajustes para la Construcción Modular de Formularios por Bloques.
 * Permite a los administradores diseñar, reordenar y personalizar formularios.
 */
class FormSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.form-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Ajustes';

    protected static ?string $navigationLabel = 'Constructor de Formularios';

    protected static ?string $title = 'Constructor de Formularios';

    protected static ?int $navigationSort = 2;

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->seesAllRecords() ?? false;
    }

    public function getSubheading(): ?string
    {
        return 'Personaliza y construye los campos y secciones de los formularios mediante bloques modulares con arrastrar y soltar.';
    }

    public function mount(): void
    {
        $values = [];

        foreach (array_keys(CustomForm::FORMS) as $key) {
            $formRecord = CustomForm::getByKey($key);
            $values[$key] = [
                'is_active' => $formRecord?->is_active ?? true,
                'name' => $formRecord?->name ?? CustomForm::FORMS[$key]['name'],
                'description' => $formRecord?->description ?? CustomForm::FORMS[$key]['description'],
                'blocks' => $formRecord?->blocks ?? CustomForm::defaultBlocksFor($key),
            ];
        }

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        $tabs = [];

        foreach (CustomForm::FORMS as $key => $info) {
            $tabs[] = Tabs\Tab::make($info['name'])
                ->schema([
                    Section::make("Ajustes generales - {$info['name']}")
                        ->description($info['description'])
                        ->columns(2)
                        ->schema([
                            Toggle::make("{$key}.is_active")
                                ->label('Formulario habilitado')
                                ->helperText('Permite desactivar temporalmente este formulario.')
                                ->default(true),

                            TextInput::make("{$key}.name")
                                ->label('Nombre del formulario')
                                ->required(),

                            TextInput::make("{$key}.description")
                                ->label('Descripción interna')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Bloques de Construcción del Formulario')
                        ->description('Añade, edita y reordena los bloques para estructurar el formulario a tu gusto.')
                        ->schema([
                            $this->getBlocksBuilder("{$key}.blocks"),
                        ]),
                ]);
        }

        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Formularios')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs($tabs),
            ]);
    }

    protected function getBlocksBuilder(string $name): Builder
    {
        return Builder::make($name)
            ->label('Bloques del formulario')
            ->addActionLabel('Añadir bloque de construcción')
            ->collapsible()
            ->collapsed(false)
            ->reorderable()
            ->cloneable()
            ->blocks([
                Block::make('text_input')
                    ->label('📝 Campo de Texto Corto')
                    ->icon('heroicon-o-pencil')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Identificador del campo (slug/código)')
                            ->placeholder('ej: nombre, telefono, presupuesto')
                            ->required()
                            ->alphaDash(),

                        TextInput::make('label')
                            ->label('Etiqueta visible')
                            ->placeholder('ej: Nombre completo, Teléfono')
                            ->required(),

                        TextInput::make('placeholder')
                            ->label('Texto de marcador (Placeholder)')
                            ->placeholder('ej: Escribe tu nombre aquí...'),

                        Select::make('input_type')
                            ->label('Tipo de entrada')
                            ->options([
                                'text' => 'Texto general',
                                'email' => 'Correo electrónico',
                                'tel' => 'Teléfono / WhatsApp',
                                'number' => 'Numérico / Monto',
                                'url' => 'Enlace / URL',
                            ])
                            ->default('text')
                            ->required(),

                        Select::make('width')
                            ->label('Ancho en pantalla')
                            ->options([
                                'full' => 'Ancho completo (100%)',
                                'half' => 'Mitad de ancho (50%)',
                                'third' => 'Un tercio (33%)',
                            ])
                            ->default('full')
                            ->required(),

                        Toggle::make('required')
                            ->label('Campo obligatorio')
                            ->default(false),

                        TextInput::make('helper_text')
                            ->label('Texto de ayuda inferior')
                            ->columnSpanFull(),
                    ]),

                Block::make('textarea')
                    ->label('📄 Área de Texto / Mensaje')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Identificador del campo')
                            ->placeholder('ej: mensaje, comentarios')
                            ->required()
                            ->alphaDash(),

                        TextInput::make('label')
                            ->label('Etiqueta visible')
                            ->placeholder('ej: Mensaje adicional')
                            ->required(),

                        TextInput::make('placeholder')
                            ->label('Placeholder'),

                        TextInput::make('rows')
                            ->label('Número de líneas (filas)')
                            ->numeric()
                            ->default(3)
                            ->minValue(2)
                            ->maxValue(10),

                        Select::make('width')
                            ->label('Ancho en pantalla')
                            ->options([
                                'full' => 'Ancho completo (100%)',
                                'half' => 'Mitad de ancho (50%)',
                            ])
                            ->default('full'),

                        Toggle::make('required')
                            ->label('Obligatorio')
                            ->default(false),

                        TextInput::make('helper_text')
                            ->label('Texto de ayuda')
                            ->columnSpanFull(),
                    ]),

                Block::make('select')
                    ->label('📋 Lista de Selección (Dropdown)')
                    ->icon('heroicon-o-list-bullet')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Identificador del campo')
                            ->placeholder('ej: tipo_operacion')
                            ->required()
                            ->alphaDash(),

                        TextInput::make('label')
                            ->label('Etiqueta')
                            ->placeholder('ej: ¿Qué tipo de inmueble buscas?')
                            ->required(),

                        TextInput::make('placeholder')
                            ->label('Texto inicial')
                            ->placeholder('ej: Selecciona una opción...'),

                        Select::make('width')
                            ->label('Ancho')
                            ->options([
                                'full' => 'Ancho completo (100%)',
                                'half' => 'Mitad de ancho (50%)',
                            ])
                            ->default('full'),

                        Toggle::make('required')
                            ->label('Obligatorio')
                            ->default(false),

                        Repeater::make('options')
                            ->label('Opciones del menú')
                            ->columnSpanFull()
                            ->addActionLabel('Añadir opción')
                            ->reorderable()
                            ->defaultItems(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Texto visible')
                                    ->placeholder('ej: Comprar casa')
                                    ->required(),
                                TextInput::make('value')
                                    ->label('Valor guardado')
                                    ->placeholder('ej: comprar_casa')
                                    ->required(),
                            ]),
                    ]),

                Block::make('radio')
                    ->label('☑️ Opciones Radio / Casillas')
                    ->icon('heroicon-o-check-circle')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Identificador')
                            ->required()
                            ->alphaDash(),

                        TextInput::make('label')
                            ->label('Pregunta o Título')
                            ->required(),

                        Select::make('choice_type')
                            ->label('Tipo de selección')
                            ->options([
                                'radio' => 'Opción única (Radio)',
                                'checkbox' => 'Selección múltiple (Casillas)',
                            ])
                            ->default('radio')
                            ->required(),

                        Toggle::make('required')
                            ->label('Obligatorio')
                            ->default(false),

                        Repeater::make('options')
                            ->label('Opciones disponibles')
                            ->columnSpanFull()
                            ->addActionLabel('Añadir opción')
                            ->columns(2)
                            ->schema([
                                TextInput::make('label')->label('Texto')->required(),
                                TextInput::make('value')->label('Valor')->required(),
                            ]),
                    ]),

                Block::make('date')
                    ->label('📅 Selector de Fecha')
                    ->icon('heroicon-o-calendar')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Identificador')
                            ->placeholder('ej: fecha_visita')
                            ->required()
                            ->alphaDash(),

                        TextInput::make('label')
                            ->label('Etiqueta')
                            ->placeholder('ej: Fecha preferida para visita')
                            ->required(),

                        Select::make('width')
                            ->label('Ancho')
                            ->options([
                                'full' => 'Ancho completo (100%)',
                                'half' => 'Mitad de ancho (50%)',
                            ])
                            ->default('half'),

                        Toggle::make('required')
                            ->label('Obligatorio')
                            ->default(false),

                        TextInput::make('helper_text')
                            ->label('Texto de ayuda')
                            ->columnSpanFull(),
                    ]),

                Block::make('header')
                    ->label('🏷️ Encabezado / Título de Sección')
                    ->icon('heroicon-o-bookmark')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título del encabezado')
                            ->placeholder('ej: Datos de contacto')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('subtitle')
                            ->label('Subtítulo o descripción explicativa')
                            ->placeholder('ej: Completa este apartado para que podamos asignarte un asesor...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Block::make('divider')
                    ->label('➖ Línea Divisoria / Separador')
                    ->icon('heroicon-o-minus')
                    ->schema([
                        Select::make('style')
                            ->label('Estilo de línea')
                            ->options([
                                'solid' => 'Línea sólida estándar',
                                'dashed' => 'Línea punteada',
                            ])
                            ->default('solid'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $formData) {
            CustomForm::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $formData['name'] ?? CustomForm::FORMS[$key]['name'],
                    'description' => $formData['description'] ?? CustomForm::FORMS[$key]['description'],
                    'blocks' => $formData['blocks'] ?? [],
                    'is_active' => (bool) ($formData['is_active'] ?? true),
                ]
            );
        }

        Notification::make()
            ->title('Formularios actualizados')
            ->body('La estructura de bloques de los formularios ha sido guardada correctamente.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_defaults')
                ->label('Restaurar bloques por defecto')
                ->icon('heroicon-m-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Restaurar bloques iniciales')
                ->modalDescription('Esto reestablecerá los bloques de construcción a la configuración inicial de fábrica.')
                ->action(function () {
                    $values = [];
                    foreach (array_keys(CustomForm::FORMS) as $key) {
                        $defaults = CustomForm::defaultBlocksFor($key);
                        CustomForm::updateOrCreate(
                            ['key' => $key],
                            [
                                'name' => CustomForm::FORMS[$key]['name'],
                                'description' => CustomForm::FORMS[$key]['description'],
                                'blocks' => $defaults,
                                'is_active' => true,
                            ]
                        );
                        $values[$key] = [
                            'is_active' => true,
                            'name' => CustomForm::FORMS[$key]['name'],
                            'description' => CustomForm::FORMS[$key]['description'],
                            'blocks' => $defaults,
                        ];
                    }

                    $this->form->fill($values);

                    Notification::make()
                        ->title('Bloques restaurados')
                        ->body('Se han cargado los bloques de fábrica.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
