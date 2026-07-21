<?php

namespace App\Services\ESusService\RNDS\Security;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

class RndsReplayGuard
{
    public static function check(string $jti, array $context): void
    {
        $key = "rnds:replay:{$context['fingerprint']}:{$jti}";

        if (Cache::has($key)) {
            throw new RuntimeException('REPLAY DETECTADO RNDS (BLOQUEIO FEDERAL)');
        }

        Cache::put($key, [
            'jti' => $jti,
            'ip' => $context['ip'],
            'time' => now()->toIso8601String(),
        ], now()->addMinutes(10));
    }
}