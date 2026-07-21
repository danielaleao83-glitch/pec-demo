<?php

declare(strict_types=1);

namespace App\Application\Services\SOAP;

use Illuminate\Support\Facades\Log;

final class SoapAuditService
{
    public function log(
        string $action,
        string $correlationId,
        string $payloadHash,
        array $extra = []
    ): void {

        Log::channel('security')->info(
            'SOAP_FORENSIC',
            [

                'action' => $action,

                'correlation_id'
                    => $correlationId,

                'payload_hash'
                    => $payloadHash,

                'timestamp'
                    => now()->toIso8601String(),

                ...$extra
            ]
        );
    }
}