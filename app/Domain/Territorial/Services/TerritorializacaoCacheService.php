<?php

declare(strict_types=1);

namespace App\Services\Territorial;

use Closure;
use Illuminate\Support\Facades\Cache;

class TerritorializacaoCacheService
{
    private const PREFIX = 'territorializacao';

    /**
     * 🔑 CACHE KEY INDEX
     */
    public function indexKey(array $filters): string
    {
        return self::PREFIX . ':index:' . hash(
            'sha256',
            json_encode($filters)
        );
    }

    /**
     * ⚡ CACHE REMEMBER
     */
    public function remember(
        string $key,
        int $ttlMinutes,
        Closure $callback
    ): mixed {

        return Cache::remember(
            $key,
            now()->addMinutes($ttlMinutes),
            $callback
        );
    }

    /**
     * 🧹 LIMPEZA GLOBAL
     */
    public function flush(): void
    {
        Cache::tags([
            self::PREFIX,
        ])->flush();
    }
}