<?php

$isTestEnvironment = env('BELET_ENV') === 'test';

return [

    'url' => $isTestEnvironment
        ? env('BELET_TEST_URL')
        : env('BELET_PRODUCTION_URL'),

    'auth_token' => $isTestEnvironment
        ? env('BELET_TEST_AUTH_TOKEN', '')
        : env('BELET_PRODUCTION_AUTH_TOKEN', ''),

    ];
