<?php

namespace App\Enum;

enum ErrorMessage: string
{
    case PHONE_NUMBER_REQUIRED = 'phone_number_required';
    case PHONE_NUMBER_INVALID = 'phone_number_invalid';
    case PASSWORD_REQUIRED = 'password_required';
    case PASSWORD_MIN = 'password_min';
    case BANK_ID_REQUIRED = 'bank_id_is_required.';
    case BANK_NAME_REQUIRED = 'bank_name_required';
    case BANK_ID_INVALID = 'bank_id_must_be_a_valid_integer.';
    case AMOUNT_REQUIRED = 'amount_is_required.';
    case AMOUNT_INVALID = 'amount_must_be_a_numeric_value.';
    case AMOUNT_MIN = 'amount_must_be_at_least_1_manat.';
    case RETURN_URL_REQUIRED = 'return_URL_is_required.';
    case RETURN_URL_INVALID = 'return_URL_must_be_a_valid_URL.';
    case ORDER_ID_OR_PAY_ID_REQUIRED = 'either_orderId_or_pay_id_is_required.';
    case ORDER_ID_INVALID = 'orderId_must_be_a_valid_string.';
    case PAY_ID_INVALID = 'pay_id_must_be_a_valid_string.';
    case ID_REQUIRED = 'order_ID_is_required';
}
