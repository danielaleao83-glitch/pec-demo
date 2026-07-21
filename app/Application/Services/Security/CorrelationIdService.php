<?php

declare(strict_types=1);

namespace App\Application\Services\Security;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🔐 CORRELATION ID SERVICE
 * =========================================================
 *
 * ✔ UUID v7
 * ✔ Rastreamento distribuído
 * ✔ Observabilidade
 * ✔ Compatível RNDS
 * ✔ Blindagem hospitalar
 *
 * =========================================================
 */
final class CorrelationIdService
{
    private const HEADER = 'X-Correlation-ID';

    public function resolve(
        Request $request
    ): string {

        $header =
            $request->header(
                self::HEADER
            );

        if (
            is_string($header)
            && ! empty($header)
        ) {
            return $header;
        }

        return $this->generate();
    }

    public function generate(): string
    {
        return Uuid::uuid7()
            ->toString();
    }
}