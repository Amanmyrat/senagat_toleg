<?php

namespace App\Helpers;

class MoneyHelper
{
    public static function decimalToInt(float|string $amount): int
    {
        $normalized = str_replace(',', '.', trim((string) $amount));

        return (int) round((float) $normalized * 100);
    }

    public static function intToDecimal(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
