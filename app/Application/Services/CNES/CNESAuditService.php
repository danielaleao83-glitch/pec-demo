<?php

declare(strict_types=1);

namespace App\Application\Services\CNES;

use Illuminate\Support\Facades\Log;

final class CNESAuditService
{
    public function log(
        string $action,
        string $correlationId,
        array $payload = []
    ): void {

        Log::channel('security')->info(
            'CNES_AUDIT',
            [

                'action' => $action,

                'correlation_id'
                    => $correlationId,

                'payload'
                    => $payload,

                'timestamp'
                    => now()->toIso8601String(),
            ]
        );
    }
}