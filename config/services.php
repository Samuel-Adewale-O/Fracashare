<?php

return [
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

    'verify_me' => [
        'api_key' => env('VERIFY_ME_API_KEY'),
        'webhook_secret' => env('VERIFY_ME_WEBHOOK_SECRET'),
    ],

    'smile_identity' => [
        'api_key' => env('SMILE_IDENTITY_API_KEY'),
        'partner_id' => env('SMILE_IDENTITY_PARTNER_ID'),
        'webhook_secret' => env('SMILE_IDENTITY_WEBHOOK_SECRET'),
    ],

    'termii' => [
        'api_key' => env('TERMII_API_KEY'),
        'sender_id' => env('TERMII_SENDER_ID'),
    ],
];