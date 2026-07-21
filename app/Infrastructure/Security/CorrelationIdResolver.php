<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class CorrelationIdResolver
{
    public function resolve(
        Request $request
    ): string {

        return $request->header(
            'X-Correlation-ID'
        ) ?: Uuid::uuid7()->toString();
    }
}