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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

    'youtube_analytics' => [
        'client_id' => env('YOUTUBE_ANALYTICS_CLIENT_ID'),
        'client_secret' => env('YOUTUBE_ANALYTICS_CLIENT_SECRET'),
        'redirect_uri' => env('YOUTUBE_ANALYTICS_REDIRECT_URI', 'http://localhost:8010/auth/google/callback'),
        'frontend_redirect_uri' => env('FRONTEND_REDIRECT_URI', 'http://localhost:8010/auth/google/callback'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'luma' => [
        'api_key' => env('LUMA_API_KEY'),
    ],
];
