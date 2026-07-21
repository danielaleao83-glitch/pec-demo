<?php

namespace App\Jobs;

use App\Models\FilaNotificacao;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarMensagemWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 30;

    public $backoff = [10, 30, 60]; // retry progressivo

    protected string $filaId;

    /**
     * IMPORTANTE: nunca serializar Model inteiro em produção crítica
     */
    public function __construct(string $filaId)
    {
        $this->filaId = $filaId;
    }

    public function handle(WhatsAppService $service): void
    {
        $fila = FilaNotificacao::find($this->filaId);

        if (! $fila) {
            Log::warning('FilaNotificacao não encontrada', [
                'fila_id' => $this->filaId,
            ]);

            return;
        }

        /**
         * 🧠 IDEMPOTÊNCIA (evita envio duplicado)
         */
        if ($fila->status === 'enviado') {
            return;
        }

        /**
         * 🔒 BLOQUEIO DE CONCORRÊNCIA
         */
        if ($fila->status === 'processando') {
            return;
        }

        $fila->update([
            'status' => 'processando',
        ]);

        try {
            $response = $service->disparar(
                $fila->destino,
                $fila->mensagem
            );

            /**
             * ✔ SUCESSO
             */
            if (! empty($response['status']) && $response['status'] === true) {

                $fila->update([
                    'status' => 'enviado',
                    'enviado_em' => now(),
                    'erro' => null,
                ]);

                return;
            }

            /**
             * ❌ FALHA CONTROLADA
             */
            $fila->update([
                'status' => 'erro',
                'tentativas' => $fila->tentativas + 1,
                'erro' => json_encode($response),
            ]);

            throw new \Exception('Falha no envio WhatsApp');
        } catch (\Throwable $e) {

            /**
             * 🧠 RETRY INTELIGENTE (SUS LEVEL)
             */
            $fila->update([
                'status' => $fila->tentativas >= 2 ? 'falha_definitiva' : 'erro',
                'tentativas' => $fila->tentativas + 1,
                'erro' => $e->getMessage(),
            ]);

            Log::error('WhatsAppJob erro', [
                'fila_id' => $fila->id,
                'erro' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * 🚨 EXECUTADO QUANDO JOB EXPIRA
     */
    public function failed(\Throwable $e): void
    {
        $fila = FilaNotificacao::find($this->filaId);

        if ($fila) {
            $fila->update([
                'status' => 'falha_definitiva',
                'erro' => $e->getMessage(),
            ]);
        }

        Log::critical('WhatsAppJob FAILED definitivo', [
            'fila_id' => $this->filaId,
            'erro' => $e->getMessage(),
        ]);
    }
}
