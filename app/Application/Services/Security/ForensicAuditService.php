<?php

declare(strict_types=1);

namespace App\Application\Services\Security;

use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🔐 FORENSIC AUDIT SERVICE
 * =========================================================
 *
 * ✔ Auditoria federal
 * ✔ Hash SHA512
 * ✔ Correlation ID
 * ✔ LGPD
 * ✔ Rastreamento distribuído
 *
 * =========================================================
 */
final class ForensicAuditService
{
    public function log(
        string $action,
        string $correlationId,
        array $payload = []
    ): void {

        $hashIntegridade =
            hash(
                'sha512',
                json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE
                )
            );

        Log::channel('security')->info(
            'FORENSIC_AUDIT',
            [

                'audit_uuid'
                    => Uuid::uuid7()
                        ->toString(),

                'action'
                    => $action,

                'correlation_id'
                    => $correlationId,

                'payload'
                    => $payload,

                'hash_integridade'
                    => $hashIntegridade,

                'timestamp'
                    => now()
                        ->toIso8601String(),
            ]
        );
    }
}