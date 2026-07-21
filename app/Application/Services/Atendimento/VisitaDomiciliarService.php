<?php

namespace App\Services\Atendimento;

use App\Models\VisitaDomiciliar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class VisitaDomiciliarService
{
    /**
     * 📡 MARCAR COMO ENVIADO (IDEMPOTENTE + CONCORRÊNCIA SEGURA)
     */
    public function marcarComoEnviado(VisitaDomiciliar $visita): VisitaDomiciliar
    {
        return DB::transaction(function () use ($visita) {

            // 🔐 evita dupla execução concorrente
            $visita = VisitaDomiciliar::where('id', $visita->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($visita->enviado_sisab) {
                return $visita->fresh();
            }

            $visita->update([
                'enviado_sisab' => true,
                'pendente_envio' => false,
                'data_envio_sisab' => Carbon::now(),
                'uuid_envio' => (string) Str::uuid(), // 🔐 rastreio federal
            ]);

            Log::channel('audit')->info('SISAB_VISITA_ENVIADA', [
                'visita_id' => $visita->id,
                'paciente_id' => $visita->paciente_id,
                'profissional_id' => $visita->profissional_id,
                'ip' => request()->ip(),
                'timestamp' => now()->toIso8601String(),
            ]);

            return $visita->fresh();
        });
    }

    /**
     * 📤 ENVIO SISAB (FEDERAL READY + HASH + IDEMPOTÊNCIA)
     */
    public function enviarParaSISAB(VisitaDomiciliar $visita): bool
    {
        try {
            return DB::transaction(function () use ($visita) {

                // 🔐 trava concorrência
                $visita = VisitaDomiciliar::where('id', $visita->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 🔐 idempotência real
                if ($visita->enviado_sisab) {
                    Log::info('SISAB_DUPLICADO_BLOQUEADO', ['visita_id' => $visita->id]);
                    return true;
                }

                if (! $visita->domicilio_id || ! $visita->data_visita) {
                    throw new \Exception('Visita inválida para SISAB');
                }

                // 📦 payload estruturado
                $payload = [
                    'visita_id' => $visita->id,
                    'uuid' => (string) Str::uuid(),

                    'domicilio_id' => $visita->domicilio_id,
                    'familia_id' => $visita->familia_id,
                    'paciente_id' => $visita->paciente_id,
                    'profissional_id' => $visita->profissional_id,

                    'data_visita' => $visita->data_visita,
                    'tipo_visita' => $visita->tipo_visita,

                    'condicoes' => [
                        'imovel_visitado' => $visita->imovel_visitado,
                        'moradia' => $visita->situacao_moradia,
                        'higiene' => $visita->condicoes_higiene,
                    ],

                    'clinico' => [
                        'observacoes' => $visita->observacoes,
                        'conduta' => $visita->conduta,
                        'sinais_vitais' => $visita->sinais_vitais,
                    ],

                    'territorio' => [
                        'microarea' => $visita->microarea,
                        'equipe_esf' => $visita->equipe_esf,
                    ],
                ];

                // 🔐 HASH DE INTEGRIDADE (FEDERAL TRACE)
                $payload['hash_integridade'] = hash('sha256', json_encode($payload));

                Log::info('SISAB_PAYLOAD', [
                    'visita_id' => $visita->id,
                    'hash' => $payload['hash_integridade'],
                ]);

                // 🌐 ENVIO (se ativado)
                $endpoint = config('services.sisab.endpoint');

                if ($endpoint) {

                    $response = Http::timeout(15)
                        ->retry(2, 200)
                        ->post($endpoint, $payload);

                    if (! $response->successful()) {

                        Log::error('SISAB_FALHA_ENVIO', [
                            'visita_id' => $visita->id,
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);

                        return false;
                    }
                }

                // 📡 marca enviado
                $this->marcarComoEnviado($visita);

                return true;
            });

        } catch (Throwable $e) {

            Log::error('SISAB_ERRO_CRITICO', [
                'visita_id' => $visita->id,
                'erro' => $e->getMessage(),
                'trace_id' => (string) Str::uuid(),
            ]);

            return false;
        }
    }

    /**
     * 🔄 FILA SEGURA (evita corrida)
     */
    public function pendentes()
    {
        return VisitaDomiciliar::query()
            ->where('enviado_sisab', false)
            ->where('pendente_envio', true)
            ->orderBy('data_visita', 'asc')
            ->limit(500)
            ->get();
    }

    /**
     * 📊 PROCESSAMENTO LOTE (COM CONTROLE DE FALHA)
     */
    public function processarLote(): array
    {
        $pendentes = $this->pendentes();

        $sucesso = 0;
        $falha = 0;

        foreach ($pendentes as $visita) {

            try {
                $ok = $this->enviarParaSISAB($visita);

                $ok ? $sucesso++ : $falha++;

            } catch (Throwable $e) {

                $falha++;

                Log::error('SISAB_LOTE_ERRO', [
                    'visita_id' => $visita->id,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        Log::info('SISAB_LOTE_FINALIZADO', [
            'sucesso' => $sucesso,
            'falha' => $falha,
            'total' => count($pendentes),
        ]);

        return compact('sucesso', 'falha');
    }

    /**
     * 🧠 STATUS FEDERAL
     */
    public function status(VisitaDomiciliar $visita): string
    {
        return match (true) {
            (bool) $visita->enviado_sisab => 'ENVIADO',
            (bool) $visita->pendente_envio => 'PENDENTE',
            default => 'RASCUNHO',
        };
    }

    /**
     * 📊 ESTATÍSTICA SEGURA
     */
    public function estatisticas(): array
    {
        return [
            'total' => VisitaDomiciliar::count(),
            'enviadas' => VisitaDomiciliar::where('enviado_sisab', true)->count(),
            'pendentes' => VisitaDomiciliar::where('pendente_envio', true)->count(),
            'alto_risco' => VisitaDomiciliar::altoRisco()->count(),
        ];
    }
}