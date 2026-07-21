<?php

declare(strict_types=1);

namespace App\Application\Services\Atendimento\Actions\RegistrarFila;

final class GerarFilaHashAction
{
    public function execute(
        string $pacienteId,
        int $prioridade,
        string $correlationId
    ): string {

        return hash(
            'sha256',
            $this->buildFingerprint(
                $this->normalize($pacienteId),
                $this->normalize($correlationId),
                $this->normalizePriority($prioridade)
            )
        );
    }

    /**
     * 🧠 constrói assinatura determinística da fila
     */
    private function buildFingerprint(
        string $pacienteId,
        string $correlationId,
        string $prioridade
    ): string {

        return $pacienteId . '|' . $prioridade . '|' . $correlationId;
    }

    /**
     * 🧼 normalização consistente de identidade
     */
    private function normalize(string $value): string
    {
        return trim(mb_strtolower($value));
    }

    /**
     * 🔐 garante estabilidade semântica da prioridade
     */
    private function normalizePriority(int $priority): string
    {
        return str_pad((string) $priority, 2, '0', STR_PAD_LEFT);
    }
}