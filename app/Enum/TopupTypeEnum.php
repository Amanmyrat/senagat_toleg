<?php

namespace App\Enum;

enum TopupTypeEnum: string
{
    case IPTV = 'iptv';
    case INTERNET = 'internet';
    case PHONE = 'phone';
    case CDMA = 'cdma';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
