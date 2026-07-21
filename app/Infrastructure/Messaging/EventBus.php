<?php

namespace App\Infrastructure\Messaging;

class EventBus implements EventBusInterface
{
    public function dispatch(object $event): void
    {
        // aqui entra:
        // - RabbitMQ
        // - Kafka
        // - Laravel Queue
        // - RNDS Gateway

        logger()->info('EVENT_DISPATCH', [
            'event' => get_class($event),
            'payload' => $event,
        ]);
    }
}