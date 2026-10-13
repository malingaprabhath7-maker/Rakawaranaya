<?php

namespace App\Support;

final class Money
{
    public static function format(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $v = (float) $amount;
        $s = number_format($v, fmod($v, 1.0) == 0.0 ? 0 : 2);

        return $withSymbol ? 'Rs. '.$s : $s;
    }

    /** Compact figure for charts and tiles: 1250000 → "1.25M", 850000 → "850K" */
    public static function short(float|int|null $amount): string
    {
        $v = (float) $amount;
        if (abs($v) >= 1_000_000) {
            return rtrim(rtrim(number_format($v / 1_000_000, 2), '0'), '.').'M';
        }
        if (abs($v) >= 1_000) {
            return rtrim(rtrim(number_format($v / 1_000, 1), '0'), '.').'K';
        }

        return number_format($v);
    }
}
