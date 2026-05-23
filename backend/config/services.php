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

    /*
    | Public Judge0 CE — no API key required.
    | Default host: ce.judge0.com (official free public API).
    | Set JUDGE0_BASE_URL if you use another Judge0-compatible endpoint.
    */
    'judge0' => [
        'enabled' => env('JUDGE0_ENABLED', true),
        'base_url' => env('JUDGE0_BASE_URL', 'https://ce.judge0.com'),
        'api_key' => env('JUDGE0_API_KEY'),
        'timeout' => (int) env('JUDGE0_TIMEOUT', 30),
    ],

    'code_runner' => [
        'fallback_enabled' => env('CODE_RUNNER_FALLBACK', true),
        'timeout' => (int) env('CODE_RUNNER_TIMEOUT', 8),
    ],

];
