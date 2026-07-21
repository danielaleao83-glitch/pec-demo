<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => 'null',
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | 🔥 CANAIS DE LOG
    |--------------------------------------------------------------------------
    */
    'channels' => [

        /*
        |--------------------------------------------------------------------------
        | STACK (PRINCIPAL)
        |--------------------------------------------------------------------------
        */
        'stack' => [
            'driver' => 'stack',
            'channels' => ['json'],
            'ignore_exceptions' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | 📦 LOG JSON (PRODUÇÃO)
        |--------------------------------------------------------------------------
        */
        'json' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'error'),

            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => storage_path('logs/laravel.json'),
            ],

            'formatter' => Monolog\Formatter\JsonFormatter::class,
            'formatter_with' => [
                'batch_mode' => Monolog\Formatter\JsonFormatter::BATCH_MODE_JSON,
                'append_newline' => true,
            ],

            'tap' => [App\Logging\JsonFormatterTap::class],
        ],

        /*
        |--------------------------------------------------------------------------
        | 📄 LOG SIMPLES (DEBUG LOCAL)
        |--------------------------------------------------------------------------
        */
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        /*
        |--------------------------------------------------------------------------
        | 🚨 ALERTAS (CRÍTICOS)
        |--------------------------------------------------------------------------
        */
        'critical' => [
            'driver' => 'monolog',
            'level' => 'critical',
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => storage_path('logs/critical.log'),
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 📡 SYSLOG (OPCIONAL)
        |--------------------------------------------------------------------------
        */
        'syslog' => [
            'driver' => 'syslog',
            'level' => 'error',
        ],

        /*
        |--------------------------------------------------------------------------
        | 🌐 SLACK (ALERTA PRODUÇÃO)
        |--------------------------------------------------------------------------
        */
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'SUS Logger',
            'emoji' => ':warning:',
            'level' => 'critical',
        ],

        /*
        |--------------------------------------------------------------------------
        | ❌ NULL (descarta logs)
        |--------------------------------------------------------------------------
        */
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],
    ],
];