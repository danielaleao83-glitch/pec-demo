<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains (PRODUÇÃO CONTROLADA)
    |--------------------------------------------------------------------------
    */
    'stateful' => explode(',', env(
        'SANCTUM_STATEFUL_DOMAINS',
        'seudominio.com,app.seudominio.com'
    )),

    /*
    |--------------------------------------------------------------------------
    | Guards
    |--------------------------------------------------------------------------
    */
    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Token Expiration (OBRIGATÓRIO EM PRODUÇÃO)
    |--------------------------------------------------------------------------
    | 120 minutos = 2 horas
    */
    'expiration' => 120,

    /*
    |--------------------------------------------------------------------------
    | Token Prefix (Proteção contra vazamento em repositório)
    |--------------------------------------------------------------------------
    */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', 'esus_'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
