<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events\Services;

class EventSerializer
{
    public function serialize(
        object $event
    ): array {

        return [

            'event_class' => get_class($event),

            'payload' => method_exists(
                $event,
                'toArray'
            )
                ? $event->toArray()
                : (array) $event,
        ];
    }
}