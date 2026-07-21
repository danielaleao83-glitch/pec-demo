<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        'pgsql' => [

            'driver' => 'pgsql',

            /*
            |------------------------------------------
            | 🌐 CONEXÃO BÁSICA LOCAL OU PRODUÇÃO
            |------------------------------------------
            */
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'vida_saude'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'postgres'),

            /*
            |------------------------------------------
            | 🔤 ENCODING
            |------------------------------------------
            */
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,

            /*
            |------------------------------------------
            | 🧠 SCHEMA PADRÃO
            |------------------------------------------
            */
            'schema' => env('DB_SCHEMA', 'public'),
            'search_path' => env('DB_SCHEMA', 'public'),

            /*
            |------------------------------------------
            | 🔐 SSL DESLIGADO (LOCAL DEV)
            |------------------------------------------
            */
            'sslmode' => env('DB_SSLMODE', 'disable'),

            /*
            |------------------------------------------
            | ⚙️ PDO OPTIONS SEGURAS
            |------------------------------------------
            */
            'options' => extension_loaded('pdo_pgsql') ? array_filter([
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]) : [],
        ],

    ],

    /*
    |------------------------------------------
    | 📦 MIGRATIONS TABLE
    |------------------------------------------
    */
    'migrations' => 'migrations',

    /*
    |------------------------------------------
    | 🔴 REDIS (SE USAR FUTURO)
    |------------------------------------------
    */
    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => Str::slug(env('APP_NAME', 'laravel'), '_').'_database_',
        ],

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],
    ],

];