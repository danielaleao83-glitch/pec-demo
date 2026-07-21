<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DashboardOrchestratorService
{
    /**
     * 🧠 SNAPSHOT FEDERAL (ESTILO DATASUS / RNDS)
     */
    public function buildSnapshot($user, ?string $unidadeId, string $requestId): array
    {
        $cacheKey = "dashboard_snapshot:{$unidadeId}";

        return Cache::remember($cacheKey, 5, function () use ($user, $unidadeId, $requestId) {

            return [
                'identity' => [
                    'user_uuid' => $user?->uuid,
                    'user_id' => $user?->id,
                    'cnes' => $unidadeId,
                    'trace_id' => $requestId,
                ],

                /**
                 * 🏥 MÉTRICAS CLÍNICAS (e-SUS AB)
                 */
                'clinical_metrics' => [
                    'atendimentos_hoje' => app(MetricsService::class)->atendimentosHoje($unidadeId),
                    'triagens' => app(MetricsService::class)->triagensHoje($unidadeId),
                    'encaminhamentos' => app(MetricsService::class)->encaminhamentosHoje($unidadeId),
                    'consultas_em_andamento' => app(MetricsService::class)->emAtendimento($unidadeId),
                ],

                /**
                 * 📊 FILA OPERACIONAL (tempo real)
                 */
                'queue' => [
                    'aguardando' => app(QueueService::class)->aguardando($unidadeId),
                    'prioridade_vermelha' => app(QueueService::class)->prioridadeAlta($unidadeId),
                    'tempo_medio_espera' => app(QueueService::class)->tempoMedio($unidadeId),
                ],

                /**
                 * 👨‍⚕️ RECURSOS HUMANOS
                 */
                'workforce' => [
                    'profissionais_online' => app(HumanResourcesService::class)->online($unidadeId),
                    'guiches_ativos' => app(HumanResourcesService::class)->guichesAtivos($unidadeId),
                ],

                /**
                 * 🔥 EVENTOS RECENTES (RNDS READY STREAM)
                 */
                'events' => app(EventStreamService::class)->recent($unidadeId),

                /**
                 * 🧾 AUDITORIA FEDERAL
                 */
                'audit' => [
                    'last_events' => app(AuditService::class)->last($unidadeId),
                    'integrity_status' => 'OK',
                ],

                'generated_at' => now()->toIso8601String(),
            ];
        });
    }
}