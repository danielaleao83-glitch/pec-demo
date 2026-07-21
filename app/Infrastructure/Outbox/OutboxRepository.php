<?php

declare(strict_types=1);

return [

    'outbox_repository'
        => App\Infrastructure\Outbox\OutboxRepository::class,

    'outbox_dispatcher'
        => App\Infrastructure\Outbox\OutboxDispatcher::class,

    'batch_size' => 100,

    'retry_attempts' => 5,

    'retry_delay_seconds' => 30,

    'enable_dlq' => true,

    'enable_hash_validation' => true,

    'enable_correlation_trace' => true,

    'enable_replay' => true,

    'enable_audit_log' => true,

];