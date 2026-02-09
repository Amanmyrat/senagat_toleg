<?php

return [
    //
    //    'senagat' => [
    //        'base_url' => env('SENAGAT_BASE_URL'),
    //
    //        'credentials' => [
    //            'userName' => env('SENAGAT_CHARITY_USERNAME'),
    //            'password' => env('SENAGAT_CHARITY_PASSWORD'),
    //        ],
    //
    //        'currency' => (int) env('SENAGAT_CURRENCY', 934),
    //        'return_url' => env('SENAGAT_CHARITY_RETURN_URL'),
    //    ],
    'senagat' => [
        'key' => 'senagat',
        'base_url' => env('SENAGAT_BASE_URL'),
        'pay_endpoint' => env('CHARITY_ENDPOINT'),
        'status_endpoint' => env('STATUS_ENDPOINT'),
        'userName' => env('SENAGAT_CHARITY_USERNAME'),
        'password' => env('SENAGAT_CHARITY_PASSWORD'),
        'return_url' => env('SENAGAT_CHARITY_RETURN_URL'),
        'currency' => (int) env('CURRENCY', 934),
    ],

    'altyn_asyr' => [
        'key' => 'altyn_asyr',
        'base_url' => env('HALK_BANK_BASE_URL'),
        'pay_endpoint' => env('CHARITY_ENDPOINT'),
        'status_endpoint' => env('STATUS_ENDPOINT'),
        'userName' => env('HALK_BANK_CHARITY_USERNAME'),
        'password' => env('HALK_BANK_CHARITY_PASSWORD'),
        'return_url' => env('HALK_BANK_CHARITY_RETURN_URL'),
        'currency' => (int) env('CURRENCY', 934),
    ],
    'rysgal' => [
        'key' => 'rysgal',
        'base_url' => env('RYSGAL_BANK_BASE_URL'),
        'pay_endpoint' => env('CHARITY_ENDPOINT'),
        'status_endpoint' => env('STATUS_ENDPOINT'),
        'userName' => env('RYSGAL_BANK_CHARITY_USERNAME'),
        'password' => env('RYSGAL_BANK_CHARITY_PASSWORD'),
        'return_url' => env('RYSGAL_BANK_CHARITY_RETURN_URL'),
        'currency' => (int) env('CURRENCY', 934),
    ],

];
