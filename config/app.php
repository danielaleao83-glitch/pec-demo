<?php

return [

    'name' => env('APP_NAME', 'e-SUS APS Laravel'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | ⏱ TIMEZONE (PADRÃO GOVERNO)
    |--------------------------------------------------------------------------
    */
    'timezone' => 'UTC',

    'locale' => 'pt_BR',
    'fallback_locale' => 'pt_BR',
    'faker_locale' => 'pt_BR',

    /*
    |--------------------------------------------------------------------------
    | 🔐 CRIPTOGRAFIA
    |--------------------------------------------------------------------------
    */
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    'previous_keys' => array_filter(
        explode(',', env('APP_PREVIOUS_KEYS', ''))
    ),

    /*
    |--------------------------------------------------------------------------
    | 🔐 SEGURANÇA CENTRAL
    |--------------------------------------------------------------------------
    */
    'security' => [

        'enabled' => true,

        'force_https' => true,
        'hsts' => true,

        'secure_headers' => true,

        'key_rotation' => true,

        'encryption_strict' => true,

        // 🚨 bloqueio automático
        'ip_blocking' => true,

        // 🚨 proteção brute force global
        'anti_bruteforce' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 📜 AUDITORIA FORENSE
    |--------------------------------------------------------------------------
    */
    'audit' => [

        'enabled' => true,

        'fail_safe' => true,

        'hash_chain' => true,

        'digital_signature' => true,

        'deep_logging' => true,

        // 🔥 nível de auditoria
        'level' => 'FULL', // BASIC | FULL

        // 🚫 rotas ignoradas
        'ignore_routes' => [
            'health',
            'login',
        ],

        // 📆 retenção legal
        'retention_years' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | 📡 RNDS
    |--------------------------------------------------------------------------
    */
    'rnds' => [

        'enabled' => env('SUS_RNDS_ENABLED', false),

        'timeout' => 30,

        'retry_attempts' => 3,
        'retry_delay' => 2000,

        'correlation_header' => 'X-Correlation-ID',

        'require_client_certificate' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 🔐 LGPD (COMPLETO)
    |--------------------------------------------------------------------------
    */
    'lgpd' => [

        'enabled' => true,

        'encrypt_sensitive' => true,

        'mask_sensitive_data' => true,

        'track_sensitive_access' => true,

        'anonymize_after_days' => 3650,

        // 🧾 base legal
        'legal_basis' => 'SAUDE_PUBLICA',

        // 👤 consentimento
        'require_consent' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | 📊 OBSERVABILIDADE
    |--------------------------------------------------------------------------
    */
    'observability' => [

        'metrics_enabled' => true,

        'structured_logging' => true,

        'distributed_tracing' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 🚨 PRODUÇÃO
    |--------------------------------------------------------------------------
    */
    'production' => [

        'enforce_https' => true,

        'hsts' => true,

        'x_frame_options' => 'SAMEORIGIN',

        'xss_protection' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 🔌 PROVIDERS
    |--------------------------------------------------------------------------
    */
    'providers' => [

        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | 🔗 ALIASES
    |--------------------------------------------------------------------------
    */
    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Str' => Illuminate\Support\Str::class,
    ],

];
