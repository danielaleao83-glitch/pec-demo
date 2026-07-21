<?php

declare(strict_types=1);

return [

    'outbox_repository'
        => App\Infrastructure\Outbox\OutboxRepository::class,

    'outbox_dispatcher'
        => App\Infrastructure\Outbox\OutboxDispatcher::class,

];