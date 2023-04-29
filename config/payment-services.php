<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for all payment services the
    | application will be interacting with.
    |
    */

    'flutterwave' => [
        'base_url' => env('FLUTTERWAVE_BASE_URL'),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
    ],
];
