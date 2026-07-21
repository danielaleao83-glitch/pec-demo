<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
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
    |--------------------------------------------------------------------------
    | 🏥 INTEGRAÇÕES SUS (e-SUS APS / GOV)
    |--------------------------------------------------------------------------
    */

    // 🔥 RNDS
    'rnds' => [
        'url' => env('RNDS_URL', 'https://rnds.saude.gov.br/api'),
        'token' => env('RNDS_TOKEN', null),
        'timeout' => env('RNDS_TIMEOUT', 10),
        'retry' => env('RNDS_RETRY', 3),
        'enabled' => env('RNDS_ENABLED', true),
        'version' => env('RNDS_VERSION', 'v1'),
        'async' => env('RNDS_ASYNC', true),
    ],

    // 🏥 SISAB
    'sisab' => [
        'url' => env('SISAB_URL', 'https://sisab.saude.gov.br/api'),
        'token' => env('SISAB_TOKEN', null),
        'timeout' => env('SISAB_TIMEOUT', 10),
        'retry' => env('SISAB_RETRY', 3),
        'enabled' => env('SISAB_ENABLED', true),
        'batch_size' => env('SISAB_BATCH_SIZE', 100),
    ],

    // 💬 WHATSAPP INSTITUCIONAL
    'whatsapp' => [
        'url' => env('WHATSAPP_URL', null),
        'token' => env('WHATSAPP_TOKEN', null),
        'timeout' => env('WHATSAPP_TIMEOUT', 10),
        'enabled' => env('WHATSAPP_ENABLED', true),
        'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | 🧯 FALLBACK / CONTINGÊNCIA (OFFLINE MODE SUS)
    |--------------------------------------------------------------------------
    */

    'fallback' => [
        'enabled' => env('FALLBACK_ENABLED', true),
        'storage_days' => env('FALLBACK_STORAGE_DAYS', 7),
        'retry_interval_minutes' => env('FALLBACK_RETRY_INTERVAL', 15),
        'max_retries' => env('FALLBACK_MAX_RETRIES', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | 📡 MONITORAMENTO (PRODUÇÃO SUS)
    |--------------------------------------------------------------------------
    */

    'monitoring' => [
        'log_integrations' => env('LOG_INTEGRATIONS', true),
        'log_webhooks' => env('LOG_WEBHOOKS', true),
        'log_failures' => env('LOG_FAILURES', true),
        'log_payloads' => env('LOG_PAYLOADS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | 🔐 SEGURANÇA INTEGRAÇÕES
    |--------------------------------------------------------------------------
    */

    'security' => [
        'encrypt_payloads' => env('ENCRYPT_PAYLOADS', true),
        'verify_webhooks' => env('VERIFY_WEBHOOKS', true),
        'signature_header' => env('SIGNATURE_HEADER', 'X-SIGNATURE'),
    ],

];
