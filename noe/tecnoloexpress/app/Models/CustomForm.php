<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'name', 'description', 'blocks', 'is_active'])]
class CustomForm extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public const FORMS = [
        'portal_contact' => [
            'name' => 'Formulario de Contacto General (Portada)',
            'description' => 'Formulario de captación que se muestra en la portada del portal público.',
        ],
        'property_inquiry' => [
            'name' => 'Formulario de Consulta de Inmueble',
            'description' => 'Formulario para solicitar información o agendar visita desde la ficha de una propiedad.',
        ],
        'owner_listing' => [
            'name' => 'Formulario de Captación de Propietarios',
            'description' => 'Formulario para propietarios interesados en vender o alquilar sus propiedades.',
        ],
    ];

    public static function defaultBlocksFor(string $key): array
    {
        return match ($key) {
            'portal_contact' => [
                [
                    'type' => 'header',
                    'data' => [
                        'title' => '¿Buscas algo específico?',
                        'subtitle' => 'Déjanos tus datos y un asesor te contactará con opciones a tu medida.',
                    ],
                ],
                [
                    'type' => 'text_input',
                    'data' => [
                        'name' => 'name',
                        'label' => 'Nombre completo',
                        'placeholder' => 'Ej. María Pérez',
                        'input_type' => 'text',
                        'required' => true,
                        'width' => 'full',
                    ],
                ],
                [
                    'type' => 'text_input',
                    'data' => [
                        'name' => 'email',
                        'label' => 'Correo electrónico',
                        'placeholder' => 'maria@ejemplo.com',
                        'input_type' => 'email',
                        'required' => true,
                        'width' => 'half',
                    ],
                ],
                [
                    'type' => 'text_input',
                    'data' => [
                        'name' => 'phone',
                        'label' => 'Teléfono / WhatsApp',
                        'placeholder' => '+593 99 123 4567',
                        'input_type' => 'tel',
                        'required' => true,
                        'width' => 'half',
                    ],
                ],
                [
                    'type' => 'select',
                    'data' => [
                        'name' => 'operation_type',
                        'label' => '¿Qué buscas?',
                        'placeholder' => 'Selecciona una opción',
                        'required' => false,
                        'width' => 'half',
                        'options' => [
                            ['label' => 'Comprar un inmueble', 'value' => 'comprar'],
                            ['label' => 'Alquilar un inmueble', 'value' => 'alquilar'],
                            ['label' => 'Invertir en proyectos', 'value' => 'invertir'],
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'data' => [
                        'name' => 'property_type',
                        'label' => 'Tipo de propiedad',
                        'placeholder' => 'Selecciona el tipo',
                        'required' => false,
                        'width' => 'half',
                        'options' => [
                            ['label' => 'Casa', 'value' => 'casa'],
                            ['label' => 'Departamento / Piso', 'value' => 'departamento'],
                            ['label' => 'Terreno / Lote', 'value' => 'terreno'],
                            ['label' => 'Oficina / Local comercial', 'value' => 'comercial'],
                        ],
                    ],
                ],
                [
                    'type' => 'textarea',
                    'data' => [
                        'name' => 'message',
                        'label' => 'Detalles o requerimientos específicos',
                        'placeholder' => 'Presupuesto aproximado, zonas de preferencia, número de habitaciones...',
                        'rows' => 3,
                        'required' => false,
                        'width' => 'full',
                    ],
                ],
            ],
            'property_inquiry' => [
                [
                    'type' => 'text_input',
                    'data' => [
                        'name' => 'name',
                        'label' => 'Tu nombre completo',
                        'placeholder' => 'Ej. Carlos Gómez',
                        'input_type' => 'text',
                        'required' => true,
                        'width' => 'full',
                    ],
                ],
                [
                    'type' => 'text_input',
                    'data' => [
                        'name' => 'phone',
                        'label' => 'Teléfono o WhatsApp',
                        'placeholder' => '+593 99 000 0000',
                        'input_type' => 'tel',
                        'required' => true,
                        'width' => 'half',
                    ],
                ],
                [
                    'type' => 'text_input',
                    'data' => [
                        'name' => 'email',
                        'label' => 'Correo electrónico',
                        'placeholder' => 'carlos@ejemplo.com',
                        'input_type' => 'email',
                        'required' => false,
                        'width' => 'half',
                    ],
                ],
                [
                    'type' => 'date',
                    'data' => [
                        'name' => 'preferred_date',
                        'label' => 'Fecha sugerida para visita',
                        'required' => false,
                        'width' => 'full',
                    ],
                ],
                [
                    'type' => 'textarea',
                    'data' => [
                        'name' => 'message',
                        'label' => 'Mensaje adicional',
                        'placeholder' => 'Hola, me interesa este inmueble y quisiera agendar una visita...',
                        'rows' => 2,
                        'required' => false,
                        'width' => 'full',
                    ],
                ],
            ],
            default => [],
        };
    }

    public static function getByKey(string $key): ?self
    {
        return Cache::rememberForever("custom_form_{$key}", function () use ($key) {
            $form = static::where('key', $key)->first();

            if (! $form && array_key_exists($key, self::FORMS)) {
                $form = static::create([
                    'key' => $key,
                    'name' => self::FORMS[$key]['name'],
                    'description' => self::FORMS[$key]['description'],
                    'blocks' => self::defaultBlocksFor($key),
                    'is_active' => true,
                ]);
            }

            return $form;
        });
    }

    protected static function booted(): void
    {
        static::saved(function (CustomForm $form) {
            Cache::forget("custom_form_{$form->key}");
        });

        static::deleted(function (CustomForm $form) {
            Cache::forget("custom_form_{$form->key}");
        });
    }
}
