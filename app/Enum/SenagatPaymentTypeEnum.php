<?php


namespace App\Enum;

enum SenagatPaymentTypeEnum: string
{
    case CARD = 'card';
    case CERTIFICATE = 'certificate';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
