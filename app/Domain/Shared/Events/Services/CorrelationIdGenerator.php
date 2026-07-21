<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events\Services;

use Ramsey\Uuid\Uuid;

class CorrelationIdGenerator
{
    public function generate(): string
    {
        return Uuid::uuid7()->toString();
    }
}