<?php

namespace App\Support;

use BackedEnum;

/**
 * El estado de un formulario Filament puede contener el valor escalar del enum
 * (venido del request) o la instancia (venida del cast del modelo o de un
 * `default()`). Este helper acepta ambos y devuelve siempre la instancia.
 */
class EnumValue
{
    /**
     * @template T of BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return T|null
     */
    public static function of(string $enum, mixed $value): ?BackedEnum
    {
        if ($value instanceof $enum) {
            return $value;
        }

        if (is_string($value) || is_int($value)) {
            return $enum::tryFrom($value);
        }

        return null;
    }

    /**
     * Compara el estado con un enum sin importar en qué forma venga.
     *
     * @param  BackedEnum|array<BackedEnum>  $expected
     */
    public static function is(mixed $value, BackedEnum|array $expected): bool
    {
        $expected = is_array($expected) ? $expected : [$expected];

        foreach ($expected as $case) {
            if ($value === $case || $value === $case->value) {
                return true;
            }
        }

        return false;
    }
}
