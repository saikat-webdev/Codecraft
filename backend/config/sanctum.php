<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:5173,127.0.0.1:5173')),
    'expiration' => null,
    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    ],
    'prefix' => 'sanctum',
    'guard' => ['web'],
    'cookie' => 'XSRF-TOKEN',
    'domain' => env('SESSION_DOMAIN', null),
];
