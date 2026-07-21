<?php

namespace App\Services\Intelligence\RiskEngine;

use Illuminate\Support\Facades\Cache;

class RiskEngine
{
    public function analyze(array $context): array
    {
        $score = 0;
        $signals = [];

        /*
        |--------------------------------------------------------------------------
        | 🧠 IDENTIDADE
        |--------------------------------------------------------------------------
        */
        if (empty($context['user_id'])) {
            $score += 30;
            $signals[] = 'USER_MISSING';
        }

        /*
        |--------------------------------------------------------------------------
        | 🌐 REDE / IP
        |--------------------------------------------------------------------------
        */
        if (isset($context['ip'])) {

            if ($this->isSuspiciousIp($context['ip'])) {
                $score += 40;
                $signals[] = 'IP_SUSPICIOUS';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 🏥 DADOS CLÍNICOS
        |--------------------------------------------------------------------------
        */
        if (isset($context['action'])) {

            if (in_array($context['action'], ['exportar_sus', 'sync_rnds'])) {
                $score += 35;
                $signals[] = 'HIGH_SENSITIVITY_ACTION';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 🔁 FREQUÊNCIA
        |--------------------------------------------------------------------------
        */
        $key = 'risk:freq:' . md5($context['user_id'] ?? $context['ip']);

        $count = Cache::increment($key);

        Cache::put($key, $count, now()->addMinutes(5));

        if ($count > 50) {
            $score += 25;
            $signals[] = 'HIGH_FREQUENCY';
        }

        /*
        |--------------------------------------------------------------------------
        | 🎯 SCORE FINAL
        |--------------------------------------------------------------------------
        */

        return [
            'score' => min($score, 100),
            'level' => $this->classify($score),
            'signals' => $signals,
        ];
    }

    private function classify(int $score): string
    {
        return match (true) {
            $score >= 80 => 'CRITICAL',
            $score >= 50 => 'HIGH',
            $score >= 20 => 'MEDIUM',
            default => 'LOW',
        };
    }

    private function isSuspiciousIp(string $ip): bool
    {
        return in_array($ip, [
            '0.0.0.0',
            '127.0.0.1',
        ]) === false && str_starts_with($ip, '192.168') === false;
    }
}