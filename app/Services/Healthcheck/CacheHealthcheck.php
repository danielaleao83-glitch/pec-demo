<?php

declare(strict_types=1);

namespace App\Services\Healthcheck;

use Illuminate\Support\Facades\Cache;

class CacheHealthcheck
{
    public function check(): array
    {
        try {
            $key = 'healthcheck_' . now()->timestamp;

            Cache::put($key, 'ok', 5);

            $value = Cache::get($key);

            return [
                'status' => $value === 'ok' ? 'up' : 'down',
            ];

        } catch (\Throwable $e) {
            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }
}