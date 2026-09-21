<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DITUSI
    |--------------------------------------------------------------------------
    */

    'base_url' => env(
        'DITUSI_BASE_URL',
        'https://api.ditusi.co.id/api/dev/v1'
    ),

    'access_token_url' => env(
        'DITUSI_ACCESS_TOKEN_URL',
        'https://api.ditusi.co.id/api/v1/access-token'
    ),

    'timeout' => (int) env(
        'DITUSI_TIMEOUT',
        30
    ),

    /*
    |--------------------------------------------------------------------------
    | DEFAULT ACCOUNT
    |--------------------------------------------------------------------------
    */

    'default_account' => env(
        'DITUSI_DEFAULT_ACCOUNT',
        'primary'
    ),

    /*
    |--------------------------------------------------------------------------
    | ACCOUNTS
    |--------------------------------------------------------------------------
    */

    'accounts' => [

        'primary' => [

            'client_id' => env(
                'DITUSI_CLIENT_ID'
            ),

            'client_key' => env(
                'DITUSI_CLIENT_KEY'
            ),

            'webhook_token' => env(
                'DITUSI_WEBHOOK_TOKEN'
            ),

        ],


        'secondary' => [

            'client_id' => env(
                'DITUSI_SECONDARY_CLIENT_ID'
            ),

            'client_key' => env(
                'DITUSI_SECONDARY_CLIENT_KEY'
            ),

            'webhook_token' => env(
                'DITUSI_SECONDARY_WEBHOOK_TOKEN'
            ),

        ],

    ],

];