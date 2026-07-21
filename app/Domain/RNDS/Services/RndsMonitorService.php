<?php

namespace App\Services\IESusService\RNDS;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class RndsMonitorService
{
    /**
     * 📡 REGISTRO PRINCIPAL RNDS (AUDITORIA FEDERAL)
     */
    public function registrar($atendimento, array $response): void
    {
        $traceId = (string) Str::uuid();

        try {
            $payloadHash = hash('sha256', json_encode($response));

            // 🔐 IDEMPOTÊNCIA (evita duplicação de log)
            if ($this->jaRegistrado($atendimento->id, $payloadHash)) {
                return;
            }

            $status = $response['status'] ? 'sucesso' : 'erro';

            $row = [
                'trace_id'        => $traceId,
                'atendimento_id'  => $atendimento->id,
                'status'          => $status,
                'http_code'       => $response['http_code'] ?? null,
                'payload_hash'    => $payloadHash,
                'response'        => json_encode($this->sanitizar($response)),
                'created_at'      => now(),
            ];

            // 🔗 CHAIN HASH (integridade encadeada)
            $row['chain_hash'] = $this->gerarChainHash($row);

            DB::table('rnds_logs')->insert($row);

            // 🧾 AUDITORIA FEDERAL
            $this->audit('RNDS_LOG_OK', $row);

            // 💾 cache anti-replay log
            Cache::put("rnds:log:{$atendimento->id}:{$payloadHash}", true, 300);

        } catch (Throwable $e) {

            $this->registrarErroFinal($atendimento, $e->getMessage(), $traceId);
        }
    }

    /**
     * 🚨 ERRO FINAL (FALHA CRÍTICA RNDS)
     */
    public function registrarErroFinal($atendimento, string $erro, ?string $traceId = null): void
    {
        $traceId = $traceId ?? (string) Str::uuid();

        $row = [
            'trace_id'       => $traceId,
            'atendimento_id' => $atendimento->id ?? null,
            'status'         => 'falha_critica',
            'response'       => $erro,
            'created_at'     => now(),
        ];

        $row['chain_hash'] = $this->gerarChainHash($row);

        DB::table('rnds_logs')->insert($row);

        $this->security('RNDS_LOG_ERROR', $row);
    }

    /**
     * 🔐 IDEMPOTÊNCIA REAL
     */
    protected function jaRegistrado($atendimentoId, $hash): bool
    {
        return Cache::has("rnds:log:{$atendimentoId}:{$hash}");
    }

    /**
     * 🧬 SANITIZAÇÃO (remove dados sensíveis SUS)
     */
    protected function sanitizar(array $data): array
    {
        unset(
            $data['senha'],
            $data['token'],
            $data['cpf'],
            $data['cns']
        );

        return $data;
    }

    /**
     * 🔗 CHAIN HASH (IMUTABILIDADE FEDERAL)
     */
    protected function gerarChainHash(array $row): string
    {
        $last = Cache::get('rnds:logs:last_hash');

        $hash = hash('sha256', json_encode($row) . $last);

        Cache::put('rnds:logs:last_hash', $hash, 3600);

        return $hash;
    }

    /**
     * 🧾 AUDITORIA FEDERAL (SISTEMA DE SAÚDE)
     */
    protected function audit(string $event, array $data): void
    {
        Log::channel('audit')->info($event, [
            'event'      => $event,
            'data'       => $data,
            'timestamp'  => now()->toIso8601String(),
            'system'     => 'RNDS_MONITOR',
        ]);
    }

    /**
     * 🚨 LOG DE SEGURANÇA (FALHA CRÍTICA)
     */
    protected function security(string $event, array $data): void
    {
        Log::channel('security')->critical($event, [
            'event'     => $event,
            'data'      => $data,
            'timestamp' => now()->toIso8601String(),
            'system'    => 'RNDS_MONITOR',
        ]);
    }
}