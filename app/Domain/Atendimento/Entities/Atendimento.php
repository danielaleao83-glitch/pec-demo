<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Atendimento
    |--------------------------------------------------------------------------
    */

    'atendimento' => [

        'aggregate' => App\Domain\Atendimento\Entities\Atendimento::class,

        'state_machine' => App\Domain\Atendimento\State\AtendimentoStateMachine::class,

        'repository' => App\Domain\Atendimento\Repositories\AtendimentoRepositoryInterface::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Store
    |--------------------------------------------------------------------------
    */

    'event_store' => [

        'enabled' => true,

        'table' => 'event_store',

        'snapshot_table' => 'event_snapshots',

        'hash_algorithm' => 'sha256',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auditoria
    |--------------------------------------------------------------------------
    */

    'audit' => [

        'enabled' => true,

        'hash_chain' => true,

        'immutable_log' => true,

        'retention_years' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Outbox
    |--------------------------------------------------------------------------
    */

    'outbox' => [

        'enabled' => true,

        'table' => 'outbox_messages',
    ],

    /*
    |--------------------------------------------------------------------------
    | RNDS
    |--------------------------------------------------------------------------
    */

    'rnds' => [

        'enabled' => true,

        'fhir_version' => 'R4',
    ],

    /*
    |--------------------------------------------------------------------------
    | LGPD
    |--------------------------------------------------------------------------
    */

    'lgpd' => [

        'audit_access' => true,

        'audit_changes' => true,

        'encrypt_sensitive_data' => true,
    ],

];