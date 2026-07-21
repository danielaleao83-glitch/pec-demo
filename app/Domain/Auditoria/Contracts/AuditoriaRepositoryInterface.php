<?php

declare(strict_types=1);

namespace App\Domain\Auditoria\Contracts;

use App\Domain\Auditoria\Entities\AuditoriaLog;

/**
 * =========================================================
 * 🔐 AUDITORIA FEDERADA (APPEND-ONLY + VERIFICÁVEL)
 * =========================================================
 *
 * 🏥 NÍVEL: SISTEMA DE SAÚDE CRÍTICO (e-SUS / RNDS-like)
 *
 * ✔ Append-only ledger imutável
 * ✔ Rastreamento por correlation_id (fluxo clínico)
 * ✔ Replay completo de eventos
 * ✔ Verificação de integridade criptográfica
 * ✔ Compatível com Outbox Pattern
 * ✔ Compatível com Event Sourcing
 * ✔ Auditoria forense (nível legal)
 * ✔ Suporte a verificação externa (federado)
 *
 * =========================================================
 */
interface AuditoriaRepositoryInterface
{
    // =========================================================
    // 🧾 WRITE LAYER (IMUTÁVEL / LEDGER)
    // =========================================================

    /**
     * Registra um evento de auditoria de forma IMUTÁVEL.
     * Nunca atualiza. Nunca remove.
     */
    public function append(AuditoriaLog $log): void;

    // =========================================================
    // 🔍 READ BY ID (TRACE UNITÁRIO)
    // =========================================================

    /**
     * Recupera um evento específico de auditoria.
     */
    public function findById(string $id): ?AuditoriaLog;

    // =========================================================
    // 🧬 REPLAY DE AGREGADO (FONTE FORENSE)
    // =========================================================

    /**
     * Reconstrói toda a linha do tempo de um agregado.
     */
    public function findByAggregate(
        string $aggregateType,
        string $aggregateId
    ): array;

    // =========================================================
    // 🌐 CORRELATION TRACE (JORNADA CLÍNICA COMPLETA)
    // =========================================================

    /**
     * Recupera todos os eventos ligados a uma jornada clínica.
     * (Paciente / Atendimento / Fluxo SUS)
     */
    public function findByCorrelationId(string $correlationId): array;

    // =========================================================
    // 📡 STREAMING DE LEDGER (AUDITORIA EM TEMPO REAL)
    // =========================================================

    /**
     * Stream contínuo de eventos de auditoria.
     * Usado por workers, integrações e monitoramento.
     */
    public function stream(
        int $limit = 100,
        ?string $afterId = null
    ): array;

    // =========================================================
    // 🔐 INTEGRIDADE CRIPTOGRÁFICA (ANTI-TAMPER)
    // =========================================================

    /**
     * Verifica se a cadeia de auditoria não foi violada.
     * (hash chain / HMAC / ledger validation)
     */
    public function verifyIntegrity(string $aggregateId): bool;

    // =========================================================
    // 🧠 VERIFICAÇÃO FEDERADA (RNDS / AUDITOR EXTERNO)
    // =========================================================

    /**
     * Permite validação externa do ledger (governo / auditoria).
     */
    public function verifyFederatedIntegrity(
        string $aggregateId,
        string $externalSignature
    ): bool;

    // =========================================================
    // 🔁 REPLAY TOTAL DO SISTEMA (FORENSE COMPLETA)
    // =========================================================

    /**
     * Reconstrói o estado completo a partir do ledger.
     */
    public function replayAll(): array;

    // =========================================================
    // 🧾 OUTBOX COMPATIBILITY HOOK
    // =========================================================

    /**
     * Integração com Outbox Pattern (sincronização SUS/RNDS).
     */
    public function exportPendingEvents(int $limit = 100): array;
}