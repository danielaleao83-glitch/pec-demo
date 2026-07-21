<?php

namespace App\Services\Atendimento;

use App\Models\Atendimento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FilaAtendimentoService
{
    /**
     * 📋 Lista fila (somente leitura)
     */
    public function listarFilaGuiche(): Collection
    {
        return Atendimento::with('paciente')
            ->where('status', 'aguardando')
            ->orderByDesc('prioridade')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * 🚨 CHAMADA FEDERAL (ATÔMICA + AUDITORIA + EVENTO)
     */
    public function chamarProximo(int $guicheId = null, int $userId = null): ?Atendimento
    {
        return DB::transaction(function () use ($guicheId, $userId) {

            /**
             * 🔒 trava concorrente real
             */
            $atendimento = Atendimento::with('paciente')
                ->where('status', 'aguardando')
                ->orderByDesc('prioridade')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->skipLocked()
                ->first();

            if (! $atendimento) {
                return null;
            }

            /**
             * 🧠 atualiza estado clínico
             */
            $atendimento->update([
                'status' => 'chamado',
                'chamado_em' => now(),
                'guiche_id' => $guicheId,
                'chamado_por' => $userId,
            ]);

            /**
             * 🔐 LOG FORENSE (nível auditoria SUS)
             */
            Log::channel('security')->info('FILA_CHAMADA', [
                'atendimento_id' => $atendimento->id,
                'paciente_id' => $atendimento->paciente_id,
                'guiche_id' => $guicheId,
                'user_id' => $userId,
                'prioridade' => $atendimento->prioridade,
                'ip' => request()->ip(),
                'timestamp' => now()->toIso8601String(),
            ]);

            /**
             * 🧾 EVENTO RNDS / TEMPO REAL
             */
            event(new \App\Events\Atendimento\PacienteChamado($atendimento));

            return $atendimento;

        }, 3);
    }
}<?php

namespace App\Services\Atendimento;

use App\Models\Atendimento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AtendimentoService
{
    public function chamarProximo($guicheId, $userId)
    {
        return DB::transaction(function () use ($guicheId, $userId) {

            /**
             * 🔒 LOCK CONCORRENTE (nível hospital real)
             */
            $atendimento = Atendimento::with('paciente')
                ->where('status', 'aguardando')
                ->orderByDesc('prioridade')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->skipLocked()
                ->first();

            if (!$atendimento) {
                Log::warning('FILA_VAZIA', [
                    'guiche_id' => $guicheId,
                    'user_id' => $userId,
                    'timestamp' => now()->toIso8601String(),
                ]);

                return null;
            }

            /**
             * 🧠 MACHINE STATE CHECK (evita corrupção de fluxo)
             */
            if ($atendimento->status !== 'aguardando') {
                Log::error('ESTADO_INVALIDO_ATENDIMENTO', [
                    'atendimento_id' => $atendimento->id,
                    'status_atual' => $atendimento->status,
                ]);

                return null;
            }

            $now = now();

            /**
             * 📊 MÉTRICAS CLÍNICAS REAIS
             */
            $tempoEspera = $atendimento->created_at->diffInSeconds($now);

            /**
             * 🔄 ATUALIZAÇÃO ATÔMICA DO ESTADO CLÍNICO
             */
            $atendimento->update([
                'status' => 'chamado',
                'status_anterior' => 'aguardando',

                'chamado_em' => $now,
                'tempo_espera_segundos' => $tempoEspera,

                'guiche_id' => $guicheId,
                'chamado_por' => $userId,

                /**
                 * 🧾 RASTREIO CLÍNICO IMUTÁVEL
                 */
                'ultimo_evento' => 'PACIENTE_CHAMADO',
                'ultimo_evento_em' => $now,
            ]);

            /**
             * 🔐 AUDITORIA FORENSE (nível SUS federal)
             */
            Log::channel('security')->info('FILA_CHAMADA_FEDERAL', [
                'atendimento_id' => $atendimento->id,
                'paciente_id' => $atendimento->paciente_id,

                'status' => 'chamado',
                'prioridade' => $atendimento->prioridade,

                'tempo_espera_segundos' => $tempoEspera,

                'guiche_id' => $guicheId,
                'user_id' => $userId,

                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),

                'timestamp' => $now->toIso8601String(),
            ]);

            /**
             * 📡 EVENTO CLÍNICO (estilo RNDS)
             */
            event(new \App\Events\Atendimento\PacienteStatusAlterado([
                'atendimento_id' => $atendimento->id,
                'paciente_id' => $atendimento->paciente_id,
                'status' => 'chamado',
                'prioridade' => $atendimento->prioridade,
                'guiche_id' => $guicheId,
                'user_id' => $userId,
                'tempo_espera_segundos' => $tempoEspera,
                'timestamp' => $now->toIso8601String(),
            ]));

            /**
             * 📡 EVENTO DE SINCRONIZAÇÃO DE FILA (painel UBS)
             */
            event(new \App\Events\Atendimento\FilaAtualizada([
                'unidade_id' => $atendimento->unidade_id ?? null,
                'timestamp' => $now->toIso8601String(),
            ]));

            /**
             * 📊 MÉTRICAS DE FLUXO (SUS analytics)
             */
            event(new \App\Events\Atendimento\MetricasFila([
                'tempo_espera' => $tempoEspera,
                'prioridade' => $atendimento->prioridade,
            ]));

            return $atendimento;

        }, 5);
    }
}