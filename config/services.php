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

    'pagofacil' => [
        'base_url' => env('PAGOFACIL_BASE_URL', 'https://masterqr.pagofacil.com.bo'),
        'api_url' => env('PAGOFACIL_API_URL', 'https://masterqr.pagofacil.com.bo/api/services/v2'),
        'tc_token_service' => env('PAGOFACIL_TC_TOKEN_SERVICE'),
        'tc_token_secret' => env('PAGOFACIL_TC_TOKEN_SECRET'),
        'payment_method_qr' => env('PAGOFACIL_QR_PAYMENT_METHOD', 4),
        'payment_method_qr_candidates' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('PAGOFACIL_QR_PAYMENT_METHODS', '4,1,2,3,5,6,7,8,9,10'))
        ))),
        'override_amount' => env('PAGOFACIL_QR_OVERRIDE_AMOUNT'),
        'callback_url' => env('PAGOFACIL_CALLBACK_URL'),
        'response_language' => env('PAGOFACIL_RESPONSE_LANGUAGE', 'es'),
    ],

];
