<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;

class RndsRealtimeDashboardService
{
    /**
     * 🧠 SNAPSHOT RNDS (CACHE + STREAM)
     */
    public function snapshot($user, ?string $unidadeId): array
    {
        return Cache::remember("rnds:dashboard:{$unidadeId}", 2, function () use ($user, $unidadeId) {

            return [
                /**
                 * 👤 IDENTIDADE FEDERAL
                 */
                'identity' => [
                    'user_uuid' => $user?->uuid,
                    'user_id' => $user?->id,
                    'cnes' => $unidadeId,
                    'role' => $user?->role,
                ],

                /**
                 * 🏥 FILA CLÍNICA AO VIVO
                 */
                'queue_realtime' => [
                    'aguardando' => app(QueueRealtimeService::class)->waiting($unidadeId),
                    'chamados' => app(QueueRealtimeService::class)->called($unidadeId),
                    'em_atendimento' => app(QueueRealtimeService::class)->inProgress($unidadeId),
                    'prioridade_vermelha' => app(QueueRealtimeService::class)->critical($unidadeId),
                ],

                /**
                 * 🧬 FLUXO CLÍNICO RNDS
                 */
                'clinical_flow' => [
                    'triagem' => app(ClinicalFlowService::class)->triagem($unidadeId),
                    'consulta' => app(ClinicalFlowService::class)->consulta($unidadeId),
                    'procedimentos' => app(ClinicalFlowService::class)->procedimentos($unidadeId),
                    'alta' => app(ClinicalFlowService::class)->alta($unidadeId),
                ],

                /**
                 * 👨‍⚕️ RECURSOS HUMANOS EM TEMPO REAL
                 */
                'workforce' => [
                    'profissionais_online' => app(WorkforceService::class)->online($unidadeId),
                    'guiches_ativos' => app(WorkforceService::class)->guiches($unidadeId),
                    'plantao_atual' => app(WorkforceService::class)->plantao($unidadeId),
                ],

                /**
                 * 📡 EVENTOS RNDS (STREAM)
                 */
                'events' => app(RndsEventStreamService::class)->latest($unidadeId),

                /**
                 * 📊 MÉTRICAS DINÂMICAS
                 */
                'metrics' => app(RndsMetricsService::class)->live($unidadeId),

                /**
                 * 🧾 AUDITORIA FEDERAL
                 */
                'audit' => app(RndsAuditService::class)->status($unidadeId),

                'generated_at' => now()->toIso8601String(),
            ];
        });
    }
}