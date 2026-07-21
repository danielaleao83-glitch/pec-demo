<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events\Services;

class EventHashGenerator
{
    public function generate(
        object $event,
        string $correlationId
    ): string {

        return hash(
            'sha512',
            implode('|', [

                get_class($event),

                serialize($event),

                $correlationId,

                config('app.key'),
            ])
        );
    }
}