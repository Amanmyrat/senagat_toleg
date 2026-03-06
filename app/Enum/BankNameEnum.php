<?php

namespace App\Enum;

enum BankNameEnum: string
{
    case SENAGAT = 'senagat';
    case ALTYN_ASYR = 'altyn_asyr';
    case RYSGAL = 'rysgal';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
