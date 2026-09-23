<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
 * Ajustes del portal público: marca, portada, secciones, contacto, SEO y
 * mantenimiento. Todo lo que aquí se guarda lo consumen las vistas de
 * `resources/views/portal`.
 */
class PortalSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.portal-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Ajustes';

    protected static ?string $navigationLabel = 'Portal público';

    protected static ?string $title = 'Ajustes del portal';

    protected static ?int $navigationSort = 1;

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasModuleAccess('portal_settings') ?? false;
    }

    public function getSubheading(): ?string
    {
        return 'Controla cómo se ve y qué muestra la web pública, sin tocar código.';
    }

    public function mount(): void
    {
        $this->form->fill($this->currentValues());
    }

    /**
     * Estado inicial del formulario, con cada ajuste ya convertido al tipo que
     * espera su campo (booleano, entero o lista).
     */
    protected function currentValues(): array
    {
        $values = [];

        foreach (array_keys(Setting::DEFAULTS) as $key) {
            $values[$key] = match (true) {
                in_array($key, self::BOOLEAN_KEYS, true) => Setting::bool($key),
                $key === 'featured_count' => Setting::int($key, 6),
                $key === 'benefits' => Setting::list($key),
                default => Setting::get($key),
            };
        }

        return $values;
    }

    /** Ajustes que se guardan como interruptor. */
    protected const BOOLEAN_KEYS = [
        'hero_show_search',
        'hero_show_stats',
        'show_featured',
        'show_cities',
        'show_contact',
        'whatsapp_float',
        'portal_enabled',
    ];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Ajustes')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Marca')
                            ->icon('heroicon-o-swatch')
                            ->schema($this->brandTab()),

                        Tabs\Tab::make('Portada')
                            ->icon('heroicon-o-photo')
                            ->schema($this->heroTab()),

                        Tabs\Tab::make('Secciones')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema($this->sectionsTab()),

                        Tabs\Tab::make('Contacto')
                            ->icon('heroicon-o-phone')
                            ->schema($this->contactTab()),

                        Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema($this->seoTab()),

                        Tabs\Tab::make('Avanzado')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema($this->advancedTab()),
                    ]),
            ]);
    }

    protected function brandTab(): array
    {
        return [
            Section::make('Identidad')
                ->description('Aparece en la cabecera y el pie del portal.')
                ->columns(2)
                ->schema([
                    TextInput::make('company_name')
                        ->label('Nombre comercial')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('company_tagline')
                        ->label('Eslogan')
                        ->maxLength(255),

                    ColorPicker::make('brand_color')
                        ->label('Color principal')
                        ->hex()
                        ->helperText('Tiñe botones, enlaces y acentos de toda la web pública.'),
                ]),

            Section::make('Imágenes')
                ->columns(2)
                ->schema([
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->disk('public')
                        ->directory('portal')
                        ->maxSize(2048)
                        ->helperText('Si lo dejas vacío se muestran las iniciales del nombre. Ideal: PNG con fondo transparente.'),

                    FileUpload::make('favicon_path')
                        ->label('Favicon')
                        ->image()
                        ->disk('public')
                        ->directory('portal')
                        ->maxSize(512)
                        ->helperText('Icono de la pestaña del navegador. Cuadrado, 32×32 o 64×64 px.'),
                ]),
        ];
    }

    protected function heroTab(): array
    {
        return [
            Section::make('Portada')
                ->description('El bloque grande que se ve al entrar a la web.')
                ->schema([
                    TextInput::make('hero_title')
                        ->label('Titular')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('hero_subtitle')
                        ->label('Subtítulo')
                        ->rows(2)
                        ->helperText('El número de propiedades publicadas se añade solo delante de este texto.'),

                    FileUpload::make('hero_image_path')
                        ->label('Imagen de fondo')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('portal')
                        ->maxSize(4096)
                        ->helperText('Opcional. Sin imagen se usa un degradado con el color principal. Recomendado: 1920×1080 px.'),
                ]),

            Section::make('Elementos de la portada')
                ->columns(2)
                ->schema([
                    Toggle::make('hero_show_search')
                        ->label('Mostrar el buscador')
                        ->helperText('Barra de búsqueda por texto, operación y tipo.'),

                    Toggle::make('hero_show_stats')
                        ->label('Mostrar los contadores')
                        ->helperText('En venta, en alquiler y número de ciudades.'),
                ]),
        ];
    }

    protected function sectionsTab(): array
    {
        return [
            Section::make('Propiedades destacadas')
                ->columns(2)
                ->schema([
                    Toggle::make('show_featured')
                        ->label('Mostrar la sección')
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('featured_heading')
                        ->label('Título')
                        ->maxLength(255)
                        ->visible(fn ($get) => $get('show_featured')),

                    TextInput::make('featured_count')
                        ->label('Cuántas mostrar')
                        ->numeric()
                        ->minValue(3)
                        ->maxValue(12)
                        ->visible(fn ($get) => $get('show_featured')),

                    TextInput::make('featured_subheading')
                        ->label('Texto de apoyo')
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->visible(fn ($get) => $get('show_featured')),
                ]),

            Section::make('Búsqueda por zona')
                ->columns(2)
                ->schema([
                    Toggle::make('show_cities')
                        ->label('Mostrar la sección')
                        ->helperText('Botones con las ciudades que tienen servicios publicados.')
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('cities_heading')
                        ->label('Título')
                        ->maxLength(255)
                        ->visible(fn ($get) => $get('show_cities')),
                ]),

            Section::make('Bloque de contacto')
                ->schema([
                    Toggle::make('show_contact')
                        ->label('Mostrar la sección')
                        ->helperText('Si lo apagas, el portal deja de captar leads desde la portada. El formulario de cada servicio sigue activo.')
                        ->live(),

                    TextInput::make('contact_heading')
                        ->label('Título')
                        ->maxLength(255)
                        ->visible(fn ($get) => $get('show_contact')),

                    Textarea::make('contact_text')
                        ->label('Texto')
                        ->rows(3)
                        ->visible(fn ($get) => $get('show_contact')),

                    Repeater::make('benefits')
                        ->label('Ventajas de la agencia')
                        ->simple(
                            TextInput::make('benefit')
                                ->hiddenLabel()
                                ->placeholder('Ej. Asesoría sin costo ni compromiso')
                                ->maxLength(255),
                        )
                        ->addActionLabel('Añadir ventaja')
                        ->reorderable()
                        ->defaultItems(0)
                        ->visible(fn ($get) => $get('show_contact')),

                    TextInput::make('owner_cta_title')
                        ->label('Título del aviso a propietarios')
                        ->maxLength(255)
                        ->visible(fn ($get) => $get('show_contact')),

                    Textarea::make('owner_cta_text')
                        ->label('Texto del aviso a propietarios')
                        ->rows(2)
                        ->visible(fn ($get) => $get('show_contact')),
                ]),
        ];
    }

    protected function contactTab(): array
    {
        return [
            Section::make('Datos de contacto')
                ->columns(2)
                ->schema([
                    TextInput::make('company_phone')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(50),

                    TextInput::make('company_email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),

                    TextInput::make('company_address')
                        ->label('Dirección')
                        ->maxLength(255),

                    TextInput::make('business_hours')
                        ->label('Horario de atención')
                        ->maxLength(255),
                ]),

            Section::make('WhatsApp')
                ->columns(2)
                ->schema([
                    TextInput::make('company_whatsapp')
                        ->label('Número de WhatsApp')
                        ->tel()
                        ->maxLength(50)
                        ->helperText('Con código de país. Los espacios y signos se limpian solos.'),

                    Toggle::make('whatsapp_float')
                        ->label('Botón flotante en el portal')
                        ->helperText('Burbuja fija en la esquina inferior derecha.'),

                    Textarea::make('whatsapp_message')
                        ->label('Mensaje predefinido')
                        ->rows(2)
                        ->columnSpanFull()
                        ->helperText('Texto con el que se abre la conversación.'),
                ]),

            Section::make('Redes sociales')
                ->description('Deja vacío lo que no uses; solo se muestran los enlaces rellenados.')
                ->columns(2)
                ->schema([
                    TextInput::make('facebook_url')->label('Facebook')->url()->maxLength(255),
                    TextInput::make('instagram_url')->label('Instagram')->url()->maxLength(255),
                    TextInput::make('tiktok_url')->label('TikTok')->url()->maxLength(255),
                    TextInput::make('youtube_url')->label('YouTube')->url()->maxLength(255),
                    TextInput::make('linkedin_url')->label('LinkedIn')->url()->maxLength(255),
                ]),
        ];
    }

    protected function seoTab(): array
    {
        return [
            Section::make('Buscadores')
                ->schema([
                    TextInput::make('seo_title')
                        ->label('Título para buscadores')
                        ->maxLength(60)
                        ->helperText('Máximo 60 caracteres. Si lo dejas vacío se usa el nombre comercial y el eslogan.'),

                    Textarea::make('seo_description')
                        ->label('Descripción para buscadores')
                        ->rows(3)
                        ->maxLength(160)
                        ->helperText('Máximo 160 caracteres. Es el texto gris que aparece bajo el enlace en Google.'),

                    FileUpload::make('og_image_path')
                        ->label('Imagen al compartir')
                        ->image()
                        ->disk('public')
                        ->directory('portal')
                        ->maxSize(2048)
                        ->helperText('Se ve al pegar el enlace en WhatsApp o redes. Recomendado: 1200×630 px.'),
                ]),

            Section::make('Analítica')
                ->schema([
                    TextInput::make('analytics_id')
                        ->label('ID de Google Analytics')
                        ->placeholder('G-XXXXXXXXXX')
                        ->maxLength(50)
                        ->helperText('Vacío = no se carga ningún script de seguimiento.'),
                ]),
        ];
    }

    protected function advancedTab(): array
    {
        return [
            Section::make('Disponibilidad del portal')
                ->schema([
                    Toggle::make('portal_enabled')
                        ->label('Portal público activo')
                        ->live()
                        ->helperText('Al apagarlo los visitantes ven un aviso de mantenimiento. El CRM sigue funcionando con normalidad y tú, como administrador, sigues viendo el portal.'),

                    Textarea::make('maintenance_message')
                        ->label('Mensaje de mantenimiento')
                        ->rows(2)
                        ->visible(fn ($get) => ! $get('portal_enabled')),
                ]),

            Section::make('Pie de página')
                ->schema([
                    Textarea::make('footer_note')
                        ->label('Nota adicional')
                        ->rows(2)
                        ->helperText('Opcional. Por ejemplo el número de licencia de la agencia.'),
                ]),
        ];
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            // Solo se persisten claves conocidas: así una clave suelta en el
            // estado del formulario no puede colarse en la tabla.
            if (array_key_exists($key, Setting::DEFAULTS)) {
                Setting::set($key, $value);
            }
        }

        Notification::make()
            ->title('Ajustes guardados')
            ->body('El portal público ya refleja los cambios.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_portal')
                ->label('Ver portal')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(route('portal.home'))
                ->openUrlInNewTab(),

            Action::make('reset')
                ->label('Restaurar valores de fábrica')
                ->icon('heroicon-m-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Restaurar todos los ajustes')
                ->modalDescription('Se perderán los textos, colores e imágenes que hayas configurado. Los servicios y los leads no se tocan.')
                ->action(function () {
                    foreach (Setting::DEFAULTS as $key => $value) {
                        Setting::set($key, $value);
                    }

                    $this->form->fill($this->currentValues());

                    Notification::make()
                        ->title('Ajustes restaurados')
                        ->success()
                        ->send();
                }),
        ];
    }
}
