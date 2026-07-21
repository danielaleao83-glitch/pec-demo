<?php

namespace App\Infrastructure\Security;

use App\Models\Auditoria\Auditoria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AuditoriaIntelligenceService
{
    /**
     * Tempo de cache em minutos
     */
    private const CACHE_TTL = 2;

    /**
     * Limite de logs analisados
     */
    private const LIMITE_LOGS = 50;

    /**
     * 🧠 ANALISA COMPORTAMENTO DO USUÁRIO/IP
     */
    public function analisar(?string $userId, string $ip): array
    {
        $cacheKey = $this->gerarCacheKey($userId, $ip);

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_TTL),
            fn () => $this->processarAnalise($userId, $ip)
        );
    }

    /**
     * 🔑 Gera chave de cache segura
     */
    private function gerarCacheKey(?string $userId, string $ip): string
    {
        return 'audit:intel:' . md5(($userId ?? 'guest') . '|' . $ip);
    }

    /**
     * ⚙️ Processa análise principal
     */
    private function processarAnalise(?string $userId, string $ip): array
    {
        $logs = $this->buscarLogs($userId, $ip);

        if ($logs->isEmpty()) {
            return $this->respostaPadrao();
        }

        $acoes = $this->contarAcoes($logs);
        $score = $this->calcularScore($logs, $acoes);

        return [
            'score' => $score,
            'risco' => $this->classificar($score),
            'acoes' => $acoes,
            'total_logs' => $logs->count(),
        ];
    }

    /**
     * 🔎 Busca logs relevantes
     */
    private function buscarLogs(?string $userId, string $ip): Collection
    {
        return Auditoria::query()
            ->where(function ($q) use ($userId, $ip) {
                $q->where('user_id', $userId)
                  ->orWhere('ip', $ip);
            })
            ->orderByDesc('executado_em')
            ->limit(self::LIMITE_LOGS)
            ->get([
                'acao',
                'executado_em',
                'ip',
                'user_id',
            ]);
    }

    /**
     * 📊 Conta ações executadas
     */
    private function contarAcoes(Collection $logs): array
    {
        $acoes = [];

        foreach ($logs as $log) {
            $acoes[$log->acao] = ($acoes[$log->acao] ?? 0) + 1;
        }

        return $acoes;
    }

    /**
     * 🚨 Calcula score de risco
     */
    private function calcularScore(Collection $logs, array $acoes): int
    {
        $score = 0;

        if (($acoes['login'] ?? 0) > 10) {
            $score += 30;
        }

        if (($acoes['delete'] ?? 0) > 5) {
            $score += 40;
        }

        if ($logs->count() > 40) {
            $score += 20;
        }

        if ($this->multiplosIps($logs)) {
            $score += 25;
        }

        if ($this->detectaPadraoAutomatizado($logs)) {
            $score += 50;
        }

        return $score;
    }

    /**
     * 🤖 DETECÇÃO DE BOT / SCRIPT
     */
    private function detectaPadraoAutomatizado(Collection $logs): bool
    {
        if ($logs->count() < 10) {
            return false;
        }

        $intervalos = [];
        $prev = null;

        foreach ($logs as $log) {
            if ($prev) {
                $intervalos[] = $prev->executado_em->diffInSeconds($log->executado_em);
            }
            $prev = $log;
        }

        if (empty($intervalos)) {
            return false;
        }

        $variacao = count(array_unique($intervalos));

        return $variacao <= 2 && count($intervalos) >= 10;
    }

    /**
     * 🌍 Detecta múltiplos IPs
     */
    private function multiplosIps(Collection $logs): bool
    {
        return $logs
            ->pluck('ip')
            ->filter()
            ->unique()
            ->count() > 3;
    }

    /**
     * 🧠 Classificação de risco
     */
    private function classificar(int $score): string
    {
        return match (true) {
            $score >= 80 => 'CRÍTICO',
            $score >= 50 => 'ALTO',
            $score >= 20 => 'MÉDIO',
            default => 'BAIXO',
        };
    }

    /**
     * 📦 Resposta padrão
     */
    private function respostaPadrao(): array
    {
        return [
            'score' => 0,
            'risco' => 'BAIXO',
            'acoes' => [],
            'total_logs' => 0,
        ];
    }
}