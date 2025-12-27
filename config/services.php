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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'paystack' => [
        'publicKey' => env('PAYSTACK_PUBLIC_KEY'),
        'secretKey' => env('PAYSTACK_SECRET_KEY'),
        'paymentUrl' => env('PAYSTACK_PAYMENT_URL'),
    ],

    'agility' => [
        'url' => env('AGILITY_URL', 'https://thirdpartynode.theagilitysystems.com/price'),
        'email' => env('AGILITY_EMAIL'),
        'password' => env('AGILITY_PASSWORD'),
        'customer_code' => env('AGILITY_CUSTOMER_CODE', 'IND1875642'),
        'vehicle_type' => env('AGILITY_VEHICLE_TYPE', 3),
        'default_weight' => env('AGILITY_DEFAULT_WEIGHT', 5),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URL'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URL'),
    ],
    'recaptcha' => [
        'site_key' => env('GOOGLE_RECAPTCHA_KEY'),
        'secret_key' => env('GOOGLE_RECAPTCHA_SECRET'),
    ],


];
