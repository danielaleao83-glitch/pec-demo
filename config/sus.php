<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuração de Integração SUS
    |--------------------------------------------------------------------------
    */

    'esus' => [
        'enabled' => env('SUS_ESUS_ENABLED', false),
        'endpoint' => env('SUS_ESUS_ENDPOINT', null),
        'token' => env('SUS_ESUS_TOKEN', null),
        'timeout' => 30,
    ],

    'rnds' => [
        'enabled' => env('SUS_RNDS_ENABLED', false),
        'endpoint' => env('SUS_RNDS_ENDPOINT', null),
        'token' => env('SUS_RNDS_TOKEN', null),
        'timeout' => 30,
    ],

];
