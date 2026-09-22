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
            'description' => 'Formulario de captación de clientes y solicitudes de servicio en la portada pública.',
        ],
        'property_inquiry' => [
            'name' => 'Formulario de Consulta de Servicio',
            'description' => 'Formulario para solicitar información o cotización de un servicio específico.',
        ],
        'owner_listing' => [
            'name' => 'Formulario de Registro / Solicitud de Servicios',
            'description' => 'Formulario para proveedores o clientes interesados en publicar o requerir servicios.',
        ],
    ];

    public static function defaultBlocksFor(string $key): array
    {
        return match ($key) {
            'portal_contact' => [
                [
                    'type' => 'header',
                    'data' => [
                        'title' => '¿Buscas un servicio específico?',
                        'subtitle' => 'Déjanos tus datos y un asesor se comunicará contigo con una propuesta a tu medida.',
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
                        'name' => 'service_type',
                        'label' => 'Tipo de servicio requerido',
                        'placeholder' => 'Selecciona una categoría',
                        'required' => false,
                        'width' => 'half',
                        'options' => [
                            ['label' => 'Consultoría y Asesoría', 'value' => 'consultoria'],
                            ['label' => 'Servicios Profesionales', 'value' => 'profesional'],
                            ['label' => 'Desarrollo e Implementación', 'value' => 'desarrollo'],
                            ['label' => 'Mantenimiento y Soporte', 'value' => 'soporte'],
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'data' => [
                        'name' => 'service_urgency',
                        'label' => 'Urgencia / Modalidad',
                        'placeholder' => 'Selecciona modalidad',
                        'required' => false,
                        'width' => 'half',
                        'options' => [
                            ['label' => 'Inmediato / Urgente', 'value' => 'urgente'],
                            ['label' => 'Por proyecto específico', 'value' => 'proyecto'],
                            ['label' => 'Contratación mensual / Recurrente', 'value' => 'recurrente'],
                        ],
                    ],
                ],
                [
                    'type' => 'textarea',
                    'data' => [
                        'name' => 'message',
                        'label' => 'Detalles o requerimientos específicos',
                        'placeholder' => 'Describe los requerimientos del servicio que necesitas...',
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
                        'label' => 'Fecha preferida para atención / inicio',
                        'required' => false,
                        'width' => 'full',
                    ],
                ],
                [
                    'type' => 'textarea',
                    'data' => [
                        'name' => 'message',
                        'label' => 'Mensaje adicional',
                        'placeholder' => 'Hola, me interesa este servicio y quisiera cotizar / agendar atención...',
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
