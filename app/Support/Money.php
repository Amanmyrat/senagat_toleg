<?php

namespace App\Support;

class Money
{
    public static function fromCents(?int $cents): ?float
    {
        return $cents !== null ? $cents / 100 : null;
    }

    public static function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    public static function formatFromCents(
        int $cents,
        int $decimals = 2,
        string $currency = ' TMT'
    ): string {
        return number_format(
            $cents / 100,
            $decimals,
            ',',
            '.'
        )." {$currency}";
    }
}
