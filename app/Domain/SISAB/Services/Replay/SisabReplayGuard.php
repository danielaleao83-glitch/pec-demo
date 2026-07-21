<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Replay;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

class SisabReplayGuard
{
    private const WINDOW_SECONDS = 30;
    private const LOCK_TTL = 5;

    public static function check(array $context): void
    {
        $fingerprint = $context['fingerprint'] ?? null;

        if (!$fingerprint) {
            throw new RuntimeException('Fingerprint ausente no contexto SISAB');
        }

        $key = self::buildKey($fingerprint);
        $lockKey = $key . ':lock';

        Cache::lock($lockKey, self::LOCK_TTL)
            ->block(2, function () use ($key, $fingerprint) {

                if (Cache::has($key)) {
                    throw new RuntimeException('Replay SISAB detectado');
                }

                Cache::put(
                    $key,
                    [
                        'fingerprint' => $fingerprint,
                        'created_at' => microtime(true),
                        'env' => app()->environment(),
                    ],
                    now()->addSeconds(self::WINDOW_SECONDS)
                );
            });
    }

    private static function buildKey(string $fingerprint): string
    {
        return sprintf(
            'sisab:replay:%s:%s',
            app()->environment(),
            hash('sha256', $fingerprint)
        );
    }
}