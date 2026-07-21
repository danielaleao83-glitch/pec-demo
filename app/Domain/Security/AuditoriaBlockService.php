<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuditoriaBlockService
{
    private const TTL = 900; // 15 min

    /**
     * 🚨 BLOQUEIO INTELIGENTE (idempotente)
     */
    public function bloquear(?string $userId, string $ip, string $motivo, int $score): void
    {
        $key = $this->key($userId, $ip);
        $agora = now();
        $expira = now()->addSeconds(self::TTL);

        // 🔥 evita duplicação
        if (Cache::has($key)) {
            return;
        }

        // 🔥 cache imediato (tempo real)
        Cache::put($key, [
            'motivo' => $motivo,
            'score' => $score,
            'blocked_at' => $agora,
            'blocked_until' => $expira,
        ], self::TTL);

        // 💾 persistência (update ou insert)
        DB::table('blocked_access')->updateOrInsert(
            [
                'user_id' => $userId,
                'ip' => $ip,
            ],
            [
                'reason' => $motivo,
                'blocked_until' => $expira,
                'updated_at' => $agora,
                'created_at' => $agora,
            ]
        );
    }

    /**
     * 🔍 VERIFICA BLOQUEIO (cache + fallback DB)
     */
    public function isBlocked(?string $userId, string $ip): ?array
    {
        $key = $this->key($userId, $ip);

        // ⚡ 1. cache (rápido)
        if ($data = Cache::get($key)) {
            return $data;
        }

        // 🐢 2. fallback banco (caso cache caiu)
        $registro = DB::table('blocked_access')
            ->where(function ($q) use ($userId, $ip) {
                $q->where('user_id', $userId)
                  ->orWhere('ip', $ip);
            })
            ->where('blocked_until', '>', now())
            ->orderByDesc('blocked_until')
            ->first();

        if (!$registro) {
            return null;
        }

        // 🔁 reidrata cache
        $ttl = now()->diffInSeconds($registro->blocked_until, false);

        if ($ttl > 0) {
            Cache::put($key, [
                'motivo' => $registro->reason,
                'score' => null,
                'blocked_at' => now(),
                'blocked_until' => $registro->blocked_until,
            ], $ttl);
        }

        return [
            'motivo' => $registro->reason,
            'score' => null,
            'blocked_until' => $registro->blocked_until,
        ];
    }

    /**
     * 🔓 DESBLOQUEIO MANUAL (admin / sistema)
     */
    public function desbloquear(?string $userId, string $ip): void
    {
        Cache::forget($this->key($userId, $ip));

        DB::table('blocked_access')
            ->where('user_id', $userId)
            ->where('ip', $ip)
            ->delete();
    }

    /**
     * 🧠 Geração de chave consistente
     */
    private function key(?string $userId, string $ip): string
    {
        return 'blocked:' . ($userId ?? 'guest') . ':' . $ip;
    }
}