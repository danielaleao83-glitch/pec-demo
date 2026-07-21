<?php

namespace App\Services\Audit;

use Illuminate\Support\Facades\Log;

class AuditService
{
    public function status($unidadeId): array
    {
        return [
            'status' => 'ACTIVE',
            'unidade' => $unidadeId,
        ];
    }

    public function log(string $action, array $context = []): void
    {
        Log::channel('security')->info($action, [
            'timestamp' => now()->toIso8601String(),
            ...$context,
        ]);
    }
}