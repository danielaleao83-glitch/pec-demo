<?php

namespace App\Services\ESusService\RNDS\Vault;

use Illuminate\Support\Str;

class RndsContext
{
    /**
     * =========================================================
     * 🔐 CONTEXTO FEDERAL RNDS
     * =========================================================
     *
     * Objetivos:
     * - rastreabilidade
     * - anti-tamper
     * - anti-replay
     * - auditoria imutável
     * - compatibilidade cluster/load balancer
     * - estabilidade operacional
     * - baixo acoplamento
     *
     * Inspirado em padrões:
     * - RNDS
     * - e-SUS
     * - DATASUS
     * - APIs críticas governamentais
     */
    public static function make(): array
    {
        // =====================================================
        // 🌐 NETWORK
        // =====================================================
        $ip = request()->ip() ?? 'CLI';

        $userAgent = substr(
            request()->userAgent() ?? 'UNKNOWN',
            0,
            180
        );

        // =====================================================
        // 👤 IDENTIDADE
        // =====================================================
        $userId = auth()->id();

        $unitId = optional(auth()->user())->unidade_id;

        // =====================================================
        // 🔐 SEGREDO RNDS DEDICADO
        // NUNCA reutilizar APP_KEY
        // =====================================================
        $securityKey = config('services.rnds.security_key');

        if (empty($securityKey)) {
            throw new \RuntimeException(
                'RNDS_SECURITY_KEY não configurada'
            );
        }

        // =====================================================
        // 🧠 DEVICE HASH
        // Mais estável que fingerprint por IP puro
        // =====================================================
        $deviceHash = hash(
            'sha256',
            implode('|', [
                $userAgent,
                $unitId,
                config('app.env'),
            ])
        );

        // =====================================================
        // 🔐 FINGERPRINT PRINCIPAL
        // =====================================================
        $fingerprint = hash(
            'sha512',
            implode('|', [
                $userId ?? 'guest',
                $unitId ?? 'no-unit',
                $deviceHash,
                $securityKey,
            ])
        );

        // =====================================================
        // 🧾 TRACEABILITY
        // =====================================================
        $traceId = (string) Str::uuid();

        $requestId = request()->header('X-Request-ID')
            ?? (string) Str::uuid();

        $correlationId = request()->header('X-Correlation-ID')
            ?? (string) Str::uuid();

        // =====================================================
        // ⏱ TIMESTAMP UTC
        // =====================================================
        $timestamp = now()->utc()->toIso8601String();

        // =====================================================
        // 🔥 CONTEXT HASH IMUTÁVEL
        // =====================================================
        $contextHash = hash(
            'sha512',
            json_encode([
                'user_id' => $userId,
                'unit_id' => $unitId,
                'ip' => $ip,
                'ua' => $userAgent,
                'timestamp' => $timestamp,
                'trace_id' => $traceId,
            ])
        );

        // =====================================================
        // 🧬 SESSION HASH
        // =====================================================
        $sessionHash = hash(
            'sha256',
            implode('|', [
                session()->getId() ?? 'cli',
                $fingerprint,
                $timestamp,
            ])
        );

        // =====================================================
        // 🔐 CONTEXTO FINAL
        // =====================================================
        return [

            // =================================================
            // 🌐 NETWORK
            // =================================================
            'ip' => $ip,

            'user_agent' => $userAgent,

            // =================================================
            // 👤 IDENTIDADE
            // =================================================
            'user_id' => $userId,

            'unit_id' => $unitId,

            // =================================================
            // 🔐 SEGURANÇA
            // =================================================
            'fingerprint' => $fingerprint,

            'device_hash' => $deviceHash,

            'session_hash' => $sessionHash,

            'context_hash' => $contextHash,

            // =================================================
            // 🧾 TRACEABILIDADE
            // =================================================
            'trace_id' => $traceId,

            'request_id' => $requestId,

            'correlation_id' => $correlationId,

            // =================================================
            // ⏱ CONTROLE TEMPORAL
            // =================================================
            'timestamp' => $timestamp,

            'timestamp_unix' => now()->timestamp,

            // =================================================
            // 🧠 AMBIENTE
            // =================================================
            'environment' => app()->environment(),

            'app_version' => config('app.version', '1.0.0'),

            'hostname' => gethostname(),

            // =================================================
            // 🔒 FLAGS FEDERAIS
            // =================================================
            'security_level' => 'RNDS_FEDERAL',

            'trust_level' => 'HIGH',

            'audit_enabled' => true,

            'anti_replay' => true,

            'integrity_protection' => true,
        ];
    }

    /**
     * =========================================================
     * 🔍 REBUILD CONTEXT
     * =========================================================
     *
     * Utilizado para:
     * - auditoria
     * - replay forensic
     * - tracing distribuído
     * - correlação de eventos
     */
    public static function rebuild(array $data): array
    {
        return [

            'trace_id' => $data['trace_id'] ?? null,

            'request_id' => $data['request_id'] ?? null,

            'correlation_id' => $data['correlation_id'] ?? null,

            'fingerprint' => $data['fingerprint'] ?? null,

            'device_hash' => $data['device_hash'] ?? null,

            'session_hash' => $data['session_hash'] ?? null,

            'context_hash' => $data['context_hash'] ?? null,

            'user_id' => $data['user_id'] ?? null,

            'unit_id' => $data['unit_id'] ?? null,

            'ip' => $data['ip'] ?? null,

            'timestamp' => $data['timestamp'] ?? null,

            'environment' => $data['environment'] ?? null,
        ];
    }

    /**
     * =========================================================
     * 🔐 CONTEXT VALIDATION
     * =========================================================
     *
     * Verifica integridade mínima
     * do contexto operacional.
     */
    public static function validate(array $context): bool
    {
        $required = [
            'trace_id',
            'fingerprint',
            'context_hash',
            'timestamp',
        ];

        foreach ($required as $field) {
            if (
                !array_key_exists($field, $context)
                || empty($context[$field])
            ) {
                return false;
            }
        }

        return true;
    }
}