<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Integridade;

use Illuminate\Support\Facades\Cache;

class SisabIntegridadeService
{
    /**
     * 🔐 janela de cadeia hash (imutabilidade leve)
     */
    private const CACHE_TTL = 3600;

    /**
     * 🚀 gera chain hash estilo blockchain hospitalar
     */
    public static function gerarChainHash(string $hash, string $traceId): array
    {
        $key = self::buildKey($traceId);

        $previous = Cache::get($key);

        $previousHash = $previous['hash'] ?? null;

        $chainHash = hash(
            'sha512',
            $hash . '|' . ($previousHash ?? 'GENESIS')
        );

        $payload = [
            'hash' => $hash,
            'chain_hash' => $chainHash,
            'previous_hash' => $previousHash,
            'trace_id' => $traceId,
            'created_at' => time(),
        ];

        Cache::put($key, $payload, self::CACHE_TTL);

        return $payload;
    }

    /**
     * 🚨 valida integridade contínua (opcional hard-check)
     */
    public static function validar(string $hash, string $traceId): bool
    {
        $key = self::buildKey($traceId);

        $data = Cache::get($key);

        if (!$data) {
            return true;
        }

        $expected = hash(
            'sha512',
            $hash . '|' . ($data['previous_hash'] ?? 'GENESIS')
        );

        return hash_equals($data['chain_hash'], $expected);
    }

    /**
     * 🔐 isolamento por traceId + ambiente
     */
    private static function buildKey(string $traceId): string
    {
        return sprintf(
            'sisab:chain:%s:%s',
            app()->environment(),
            hash('sha256', $traceId)
        );
    }
}