<?php

return [

    'base_url' =>
        env(
            'MOOGOLD_BASE_URL',
            'https://moogold.com/wp-json/v1/api'
        ),

    'user_id' =>
        env('MOOGOLD_USER_ID'),

    'partner_id' =>
        env('MOOGOLD_PARTNER_ID'),

    'secret_key' =>
        env('MOOGOLD_SECRET_KEY'),

    'timeout' =>
        (int) env(
            'MOOGOLD_TIMEOUT',
            30
        ),

];