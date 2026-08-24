<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Deriva del color principal configurado las variantes que necesita el portal.
 *
 * El portal las expone como variables CSS (`--brand`, `--brand-dark`…) porque
 * Tailwind compila sus clases en build y no puede generar un color elegido en
 * tiempo de ejecución.
 */
class Brand
{
    public const FALLBACK = '#4f46e5';

    public static function color(): string
    {
        $color = (string) Setting::get('brand_color');

        return preg_match('/^#[0-9a-f]{6}$/i', $color) ? $color : self::FALLBACK;
    }

    /** Variante oscura para estados hover. */
    public static function dark(): string
    {
        return self::shade(self::color(), -0.18);
    }

    /** Variante muy clara para fondos suaves. */
    public static function tint(): string
    {
        return self::shade(self::color(), 0.90);
    }

    /** Blanco o casi negro, el que contraste mejor sobre el color principal. */
    public static function contrast(): string
    {
        [$r, $g, $b] = self::rgb(self::color());

        // Luminancia percibida (ITU-R BT.601).
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.6 ? '#111827' : '#ffffff';
    }

    /** Iniciales del nombre comercial, para cuando no hay logo. */
    public static function initials(): string
    {
        $words = preg_split('/\s+/', trim((string) Setting::get('company_name')), -1, PREG_SPLIT_NO_EMPTY) ?: ['C'];

        return mb_strtoupper(mb_substr($words[0], 0, 1).(isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
    }

    /**
     * Aclara ($amount > 0) u oscurece ($amount < 0) un color hexadecimal.
     */
    protected static function shade(string $hex, float $amount): string
    {
        $channels = array_map(function (int $channel) use ($amount) {
            $target = $amount > 0 ? 255 : 0;

            return (int) round($channel + ($target - $channel) * abs($amount));
        }, self::rgb($hex));

        return sprintf('#%02x%02x%02x', ...$channels);
    }

    /** @return array{0: int, 1: int, 2: int} */
    protected static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
