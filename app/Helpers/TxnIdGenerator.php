<?php

namespace App\Helpers;

class TxnIdGenerator
{
    /**
     * Max 20 digit, numeric, unique
     */
    public static function generate(): string
    {
        $time = now()->format('YmdHis');

        $random = random_int(1000, 9999);

        return $time . $random;
    }
}
