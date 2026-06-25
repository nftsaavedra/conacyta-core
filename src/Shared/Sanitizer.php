<?php

declare(strict_types=1);

namespace ConacytaCore\Shared;

final class Sanitizer
{
    public static function text(?string $value): string
    {
        return sanitize_text_field((string) $value);
    }

    public static function integer(mixed $value): int
    {
        return absint($value);
    }

    public static function float(mixed $value): float
    {
        return (float) $value;
    }

    public static function boolean(mixed $value): bool
    {
        return (bool) rest_sanitize_boolean($value);
    }

    public static function url(?string $value): string
    {
        return esc_url_raw((string) $value);
    }

    public static function arrayOfStrings(mixed $value): array
    {
        return array_map('sanitize_text_field', (array) $value);
    }

    public static function email(?string $value): string
    {
        return sanitize_email((string) $value);
    }

    public static function html(?string $value): string
    {
        return wp_kses_post((string) $value);
    }
}
