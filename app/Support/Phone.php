<?php

namespace App\Support;

final class Phone
{
    /** "077 123 4567", "+94771234567", "0094 77…" → "94771234567" (for wa.me); null if unusable */
    public static function international(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0094')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '94'.substr($digits, 1);
        } elseif (strlen($digits) === 9 && str_starts_with($digits, '7')) {
            $digits = '94'.$digits;
        }

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }

    /** "94771234567" → "077 123 4567" for display */
    public static function display(?string $number): string
    {
        $i = self::international($number);
        if ($i && str_starts_with($i, '94') && strlen($i) === 11) {
            $local = '0'.substr($i, 2);

            return substr($local, 0, 3).' '.substr($local, 3, 3).' '.substr($local, 6);
        }

        return (string) $number;
    }
}
