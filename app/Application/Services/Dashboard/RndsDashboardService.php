<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Services\Atendimento\QueueService;
use App\Services\Dashboard\MetricsService;
use App\Services\Audit\AuditService;

class RndsDashboardService
{
    public function __construct(
        private QueueService $queue,
        private MetricsService $metrics,
        private AuditService $audit
    ) {}

    /**
     * 🏥 SNAPSHOT RNDS FEDERAL
     */
    public function snapshot($user): array
    {
        $unidadeId = $user?->unidade_id;

        return [
            'system' => 'RNDS_FEDERAL',
            'user_uuid' => $user?->uuid,

            /**
             * 🏥 FILA CLÍNICA
             */
            'queue' => [
                'aguardando' => $this->queue->waiting($unidadeId),
                'chamados' => $this->queue->called($unidadeId),
                'em_atendimento' => $this->queue->inProgress($unidadeId),
            ],

            /**
             * 📊 MÉTRICAS HOSPITALARES
             */
            'metrics' => $this->metrics->live($unidadeId),

            /**
             * 🧾 AUDITORIA
             */
            'audit_status' => $this->audit->status($unidadeId),

            'generated_at' => now()->toIso8601String(),
        ];
    }
}