<?php

declare(strict_types=1);

namespace App\Services\Healthcheck;

use Illuminate\Support\Facades\Cache;

class QueueHealthcheck
{
    public function check(): array
    {
        try {
            // marcador simples de worker ativo
            $heartbeat = Cache::get('queue_heartbeat');

            return [
                'status' => $heartbeat ? 'up' : 'warning',
            ];

        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }
}