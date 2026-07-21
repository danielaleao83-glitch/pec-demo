<?php

declare(strict_types=1);

namespace App\Domain\Atendimento\State;

use App\Domain\Atendimento\Enums\StatusAtendimento;
use App\Domain\Atendimento\Events\AtendimentoStatusChanged;
use App\Domain\Atendimento\Exceptions\InvalidStatusTransitionException;

/**
 * =========================================================
 * 🏥 ATENDIMENTO STATE MACHINE
 * =========================================================
 *
 * ✔ Fluxo determinístico
 * ✔ Replay-safe
 * ✔ Event-sourcing ready
 * ✔ Integridade histórica
 * ✔ Compatível UUID v7
 * ✔ Compatível Auditoria
 * ✔ Compatível Outbox
 * ✔ Compatível RNDS
 *
 * =========================================================
 */
final class AtendimentoStateMachine
{
    /**
     * =====================================================
     * MATRIZ OFICIAL DE TRANSIÇÕES
     * =====================================================
     */
    private const TRANSITIONS = [

        'aguardando' => [
            StatusAtendimento::CHAMADO,
            StatusAtendimento::CANCELADO,
        ],

        'chamado' => [
            StatusAtendimento::EM_ATENDIMENTO,
            StatusAtendimento::CANCELADO,
        ],

        'em_atendimento' => [
            StatusAtendimento::FINALIZADO,
            StatusAtendimento::CANCELADO,
        ],

        'finalizado' => [],

        'cancelado' => [],
    ];

    /**
     * =====================================================
     * VALIDA TRANSIÇÃO
     * =====================================================
     */
    public static function canTransition(
        StatusAtendimento $from,
        StatusAtendimento $to,
        ?AtendimentoStatusChanged $lastEvent = null
    ): bool {

        if (
            $lastEvent !== null &&
            $lastEvent->to !== $from
        ) {
            return false;
        }

        return in_array(
            $to,
            self::allowedTransitions($from),
            true
        );
    }

    /**
     * =====================================================
     * GARANTE TRANSIÇÃO
     * =====================================================
     */
    public static function ensureTransition(
        StatusAtendimento $from,
        StatusAtendimento $to,
        ?AtendimentoStatusChanged $lastEvent = null
    ): void {

        if (
            !self::canTransition(
                $from,
                $to,
                $lastEvent
            )
        ) {

            throw new InvalidStatusTransitionException(
                sprintf(
                    'Transição inválida [%s -> %s]',
                    $from->value,
                    $to->value
                )
            );
        }
    }

    /**
     * =====================================================
     * TRANSIÇÕES PERMITIDAS
     * =====================================================
     *
     * @return StatusAtendimento[]
     */
    public static function allowedTransitions(
        StatusAtendimento $state
    ): array {

        return self::TRANSITIONS[
            $state->value
        ] ?? [];
    }

    /**
     * =====================================================
     * ESTADO TERMINAL
     * =====================================================
     */
    public static function isTerminal(
        StatusAtendimento $state
    ): bool {

        return empty(
            self::allowedTransitions($state)
        );
    }

    /**
     * =====================================================
     * VALIDA HISTÓRICO
     * =====================================================
     */
    public static function validateHistory(
        AtendimentoStatusChanged $previous,
        AtendimentoStatusChanged $current
    ): bool {

        return
            $previous->aggregateId() === $current->aggregateId()
            &&
            $previous->to === $current->from;
    }

    /**
     * =====================================================
     * REPLAY DE EVENTOS
     * =====================================================
     *
     * @param AtendimentoStatusChanged[] $events
     */
    public static function replay(
        array $events
    ): ?StatusAtendimento {

        if (empty($events)) {
            return null;
        }

        $currentState = null;

        foreach ($events as $index => $event) {

            if (
                !self::canTransition(
                    $event->from,
                    $event->to
                )
            ) {
                throw new InvalidStatusTransitionException(
                    'Replay contém transição inválida'
                );
            }

            if ($index > 0) {

                $previous = $events[$index - 1];

                if (
                    !self::validateHistory(
                        $previous,
                        $event
                    )
                ) {
                    throw new InvalidStatusTransitionException(
                        'Histórico de eventos corrompido'
                    );
                }
            }

            $currentState = $event->to;
        }

        return $currentState;
    }

    /**
     * =====================================================
     * MATRIZ COMPLETA
     * =====================================================
     */
    public static function transitions(): array
    {
        return self::TRANSITIONS;
    }

    /**
     * =====================================================
     * FINGERPRINT DA MÁQUINA DE ESTADOS
     * =====================================================
     */
    public static function fingerprint(): string
    {
        return hash(
            'sha256',
            json_encode(
                self::TRANSITIONS,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            )
        );
    }
}