<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Ajustes del portal público, guardados como pares clave/valor.
 *
 * Todo se almacena como texto: los booleanos como «1»/«0», las listas como
 * JSON y los archivos como la ruta dentro del disco `public`. Los accesores
 * tipados de abajo son el único sitio que conoce esa convención.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** Valor de fábrica de cada ajuste. Define además qué claves existen. */
    public const DEFAULTS = [
        // Marca
        'company_name' => 'Tecnolo Inmobiliaria',
        'company_tagline' => 'Encuentra el hogar que estás buscando',
        'logo_path' => '',
        'favicon_path' => '',
        'brand_color' => '#4f46e5',

        // Portada
        'hero_title' => 'Encuentra el hogar que estás buscando',
        'hero_subtitle' => 'Te acompañamos desde la búsqueda hasta la firma.',
        'hero_image_path' => '',
        'hero_show_search' => '1',
        'hero_show_stats' => '1',

        // Secciones y contenido
        'show_featured' => '1',
        'featured_count' => '6',
        'featured_heading' => 'Propiedades destacadas',
        'featured_subheading' => 'Selección de nuestro equipo esta semana.',
        'show_cities' => '1',
        'cities_heading' => 'Busca por zona',
        'show_contact' => '1',
        'contact_heading' => '¿Buscas algo específico?',
        'contact_text' => 'Déjanos tus datos y un asesor te enviará una selección personalizada de inmuebles que encajen con tu presupuesto y tu zona preferida.',
        'benefits' => '["Asesoría sin costo ni compromiso","Acompañamiento en visitas y negociación","Gestión completa de documentación"]',
        'owner_cta_title' => '¿Quieres vender o alquilar tu inmueble?',
        'owner_cta_text' => 'Escríbenos y valoramos tu propiedad gratis.',

        // Contacto
        'company_phone' => '+593 99 000 0000',
        'company_email' => 'contacto@tecnoloinmobiliaria.com',
        'company_address' => 'Av. Principal 123, Quito',
        'business_hours' => 'Lunes a viernes de 9:00 a 18:00',
        'company_whatsapp' => '+593990000000',
        'whatsapp_float' => '1',
        'whatsapp_message' => 'Hola, vi su web y quiero información sobre una propiedad.',

        // Redes
        'facebook_url' => '',
        'instagram_url' => '',
        'tiktok_url' => '',
        'youtube_url' => '',
        'linkedin_url' => '',

        // SEO y analítica
        'seo_title' => '',
        'seo_description' => '',
        'og_image_path' => '',
        'analytics_id' => '',

        // Avanzado
        'portal_enabled' => '1',
        'maintenance_message' => 'Estamos actualizando nuestro catálogo. Vuelve en unos minutos.',
        'footer_note' => '',
    ];

    /** @return array<string, string> */
    public static function cached(): array
    {
        return Cache::rememberForever(
            'settings',
            fn () => static::query()->pluck('value', 'key')->all(),
        );
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $stored = static::cached()[$key] ?? null;

        // Una cadena vacía guardada significa «sin valor», no «cadena vacía».
        return filled($stored) ? $stored : ($default ?? (self::DEFAULTS[$key] ?? null));
    }

    public static function bool(string $key): bool
    {
        return (static::cached()[$key] ?? self::DEFAULTS[$key] ?? '0') === '1';
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = static::get($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    /** @return array<int, string> */
    public static function list(string $key): array
    {
        $decoded = json_decode((string) static::get($key), true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'filled')) : [];
    }

    /** URL pública de un ajuste que guarda la ruta de un archivo subido. */
    public static function url(string $key): ?string
    {
        $path = static::get($key);

        return filled($path) ? Storage::disk('public')->url($path) : null;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], [
            'value' => match (true) {
                is_bool($value) => $value ? '1' : '0',
                is_array($value) => json_encode(array_values($value), JSON_UNESCAPED_UNICODE),
                default => $value === null ? '' : (string) $value,
            },
        ]);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings'));
        static::deleted(fn () => Cache::forget('settings'));
    }
}
