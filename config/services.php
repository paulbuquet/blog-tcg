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

    'tcg_api' => [
        'key' => env('TCG_API_KEY'),
        'base_url' => env('TCG_API_BASE_URL', 'https://api.tcgapi.dev/v1'),
        // Tier gratuit = 100 requêtes/jour. Usage estimé : topMovers (3 jeux) + sets (3 jeux) ≈ 6-12 req/jour.
        'daily_limit' => env('TCG_API_DAILY_LIMIT', 100),
        'openai_key' => env('OPENAI_API_KEY'),
    ],

];
