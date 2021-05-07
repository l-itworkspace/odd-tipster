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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'odd' => [
        'api_key' => '950ee564b288a68ad4c893f6b544b091',
        'api_path' => 'https://api.the-odds-api.com/v3/',
    ],

    'sport_traders' => [
        'api_path'    => 'https://api.sportradar.com/oddscomparison-rowt1/en/eu/',
        'schedule'    => 'https://schema.sportradar.com/',
        'api_key'     => 'vvtrum6ubv3z5xbu4qvqd643'
    ],

    'pinnacle' => [
        'api_path' => 'https://guest.api.arcadia.pinnacle.com/0.1',
        'login'    => 'AH1351083',
        'password' => 'mF!7J*WErR',
        'encode_type' => 'base64'
    ]

];
