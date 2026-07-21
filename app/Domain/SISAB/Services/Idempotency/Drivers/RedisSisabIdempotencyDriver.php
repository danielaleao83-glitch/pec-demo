<?php

declare(strict_types=1);

namespace App\Services\SISAB\Idempotency\Drivers;

use Illuminate\Support\Facades\Redis;

class RedisSisabIdempotencyDriver
{
    private const PREFIX = 'sisab:idempotency:';

    /**
     * EXISTE (bloqueia replay real)
     */
    public function exists(string $fingerprint): bool
    {
        return Redis::exists($this->key($fingerprint)) === 1;
    }

    /**
     * RESERVA ATÔMICA (EVITA DUPLICAÇÃO EM CONCORRÊNCIA)
     */
    public function reserve(string $fingerprint): void
    {
        $key = $this->key($fingerprint);

        $ok = Redis::set(
            $key,
            json_encode([
                'status' => 'processing',
                'timestamp' => now()->toISOString(),
            ]),
            'NX', // só seta se não existir
            'EX', 300 // TTL segurança (5 min anti lock eterno)
        );

        if (! $ok) {
            throw new \RuntimeException('Duplicate fingerprint detected (idempotency blocked)');
        }
    }

    /**
     * FINALIZA SUCESSO
     */
    public function complete(string $fingerprint): void
    {
        Redis::set(
            $this->key($fingerprint),
            json_encode([
                'status' => 'completed',
                'timestamp' => now()->toISOString(),
            ]),
            'EX',
            86400 // 24h para auditoria
        );
    }

    /**
     * FINALIZA FALHA
     */
    public function fail(string $fingerprint, string $reason): void
    {
        Redis::set(
            $this->key($fingerprint),
            json_encode([
                'status' => 'failed',
                'reason' => $reason,
                'timestamp' => now()->toISOString(),
            ]),
            'EX',
            86400
        );
    }

    /**
     * KEY PADRÃO FEDERAL
     */
    private function key(string $fingerprint): string
    {
        return self::PREFIX . $fingerprint;
    }
}