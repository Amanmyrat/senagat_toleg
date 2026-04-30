<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'telecom' => [
        'url' => env('TELECOM_BILLING_URL'),
        'secret' => env('TELECOM_BILLING_SECRET'),
    ],
    'astu' => [
        'url' => env('ASTU_BASE_URL'),
        'ip' => env('ASTU_IP', '127.0.0.1'),
    ],
    'alemtv' => [
        'base_url' => env('ALEMTV_BASE_URL'),
        'aid' => env('ALEMTV_AID'),
        'token' =>env('ALEMTV_AUTH_TOKEN'),
        'timeout'  => 20,
    ],
    'tmcell' => [
        'base_url' => env('TMCELL_BASE_URL'),
        'ps_id' => env('TMCELL_PS_ID'),
        'pfx_path' => storage_path(env('TMCELL_PFX_FILE')),
        'pfx_password' => env('TMCELL_PFX_PASSWORD'),
    ],
    'cdma' => [
        'base_url' => env('CDMA_BASE_URL'),
        'ps_id' => env('CDMA_PS_ID'),
        'pfx_username' => env('CDMA_PFX_USERNAME'),
        'pfx_password' => env('CDMA_PFX_PASSWORD'),
    ],

    'senagat_back' => [
        'webhook_url' => env('SENAGAT_BACK_WEBHOOK_URL'),
        'webhook_secret' => env('SENAGAT_BACK_WEBHOOK_SECRET'),
    ],
];
