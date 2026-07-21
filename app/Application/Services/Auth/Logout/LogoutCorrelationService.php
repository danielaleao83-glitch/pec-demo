<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Logout;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🔐 LOGOUT CORRELATION SERVICE
 * =========================================================
 */
final class LogoutCorrelationService
{
    /**
     * =========================================================
     * 🔐 RESOLVE CORRELATION ID
     * =========================================================
     */
    public function resolve(
        Request $request
    ): string {

        return $request->header(
            'X-Correlation-ID'
        ) ?: Uuid::uuid7()->toString();
    }
}