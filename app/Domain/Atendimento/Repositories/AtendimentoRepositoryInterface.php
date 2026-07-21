<?php

declare(strict_types=1);

namespace App\Domain\Atendimento\Repositories;

use App\Domain\Atendimento\Entities\Atendimento;
use App\Domain\Atendimento\ValueObjects\AtendimentoId;
use App\Domain\Shared\Events\DomainEvent;

interface AtendimentoRepositoryInterface
{
    // =====================================================
    // SNAPSHOT STORE
    // =====================================================

    public function save(
        Atendimento $atendimento
    ): void;

    public function findById(
        AtendimentoId $id
    ): ?Atendimento;

    // =====================================================
    // EVENT STORE
    // =====================================================

    public function appendEvent(
        DomainEvent $event
    ): void;

    /**
     * @return DomainEvent[]
     */
    public function findEventsByAggregateId(
        AtendimentoId $id
    ): array;

    // =====================================================
    // REPLAY ENGINE
    // =====================================================

    public function rebuildFromEvents(
        AtendimentoId $id
    ): ?Atendimento;

    // =====================================================
    // AUDITORIA
    // =====================================================

    public function verifyIntegrity(
        AtendimentoId $id
    ): bool;

    public function getLatestEventHash(
        AtendimentoId $id
    ): ?string;

    // =====================================================
    // EVENT CHAIN
    // =====================================================

    public function getEventCount(
        AtendimentoId $id
    ): int;

    public function getLastEventVersion(
        AtendimentoId $id
    ): int;

    // =====================================================
    // CORRELATION TRACE
    // =====================================================

    /**
     * @return DomainEvent[]
     */
    public function findByCorrelationId(
        string $correlationId
    ): array;
}