<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events\Logs;

use Illuminate\Support\Facades\Log;

class DomainEventLogger
{
    public function log(
        string $event,
        array $context = []
    ): void {

        Log::channel('security')->info(
            $event,
            $context
        );
    }
}