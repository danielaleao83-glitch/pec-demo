<?php

declare(strict_types=1);

namespace App\Services\Healthcheck;

use Illuminate\Support\Facades\DB;

class DatabaseHealthcheck
{
    public function check(): array
    {
        try {
            $start = microtime(true);

            DB::select('SELECT 1');

            return [
                'status' => 'up',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];

        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }
}