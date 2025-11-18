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

    'tmdb' => [
        'api_key' => env('TMDB_API_KEY', 'b269975791325223540e00bb135df4a0'),
        'access_token' => env('TMDB_ACCESS_TOKEN', 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJiMjY5OTc1NzkxMzI1MjIzNTQwZTAwYmIxMzVkZjRhMCIsIm5iZiI6MTc1NTg2NzY0NS40ODg5OTk4LCJzdWIiOiI2OGE4NjlmZGI0NWEzOGEyNWMyNjEzYWEiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.xnv3RHC3vsIOFjpfXvEBRphsRSCUrqTrLUpNcKcB7Rw'),
        'base_url' => env('TMDB_BASE_URL', 'https://api.themoviedb.org/3'),
        'image_url' => env('TMDB_IMAGE_URL', 'https://image.tmdb.org/t/p'),
    ],

    'omdb' => [
        'api_key' => env('OMDB_API_KEY', '995568f6'),
        'base_url' => env('OMDB_BASE_URL', 'https://www.omdbapi.com'),
    ],

];
