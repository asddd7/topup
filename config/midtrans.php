<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DEFAULT ACCOUNT
    |--------------------------------------------------------------------------
    */

    'default_account' => env(
        'MIDTRANS_DEFAULT_ACCOUNT',
        'primary'
    ),


    /*
    |--------------------------------------------------------------------------
    | ACCOUNTS
    |--------------------------------------------------------------------------
    */

    'accounts' => [

        'primary' => [

            'is_production' => (bool) env(
                'MIDTRANS_IS_PRODUCTION',
                false
            ),

            'merchant_id' => env(
                'MIDTRANS_MERCHANT_ID'
            ),

            'client_key' => env(
                'MIDTRANS_CLIENT_KEY'
            ),

            'server_key' => env(
                'MIDTRANS_SERVER_KEY'
            ),

        ],


        'secondary' => [

            'is_production' => (bool) env(
                'MIDTRANS_SECONDARY_IS_PRODUCTION',
                false
            ),

            'merchant_id' => env(
                'MIDTRANS_SECONDARY_MERCHANT_ID'
            ),

            'client_key' => env(
                'MIDTRANS_SECONDARY_CLIENT_KEY'
            ),

            'server_key' => env(
                'MIDTRANS_SECONDARY_SERVER_KEY'
            ),

        ],

    ],

];