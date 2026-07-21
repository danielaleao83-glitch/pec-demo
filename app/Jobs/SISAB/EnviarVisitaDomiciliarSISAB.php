<?php

namespace App\Jobs\SISAB;

use App\Models\VisitaDomiciliar;
use App\Services\SISAB\VisitaDomiciliarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnviarVisitaDomiciliarSISAB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 🔁 RETRY CONTROLADO (nível produção)
     */
    public int $tries = 5;

    /**
     * ⏱️ BACKOFF progressivo (evita sobrecarga SISAB)
     */
    public array $backoff = [10, 30, 60, 120, 300];

    /**
     * 📌 ID da visita (evita serialização pesada de Model)
     */
    protected string $visitaId;

    public function __construct(string $visitaId)
    {
        $this->visitaId = $visitaId;
    }

    /**
     * 🚦 RATE LIMIT (protege integração SISAB)
     */
    public function middleware(): array
    {
        return [
            new RateLimited('sisab'),
        ];
    }

    /**
     * 🚀 EXECUÇÃO PRINCIPAL
     */
    public function handle(VisitaDomiciliarService $service): void
    {
        $visita = VisitaDomiciliar::find($this->visitaId);

        /**
         * 🧠 Segurança: registro não existe
         */
        if (! $visita) {
            Log::warning('SISAB JOB - VISITA NÃO ENCONTRADA', [
                'visita_id' => $this->visitaId,
            ]);

            return;
        }

        /**
         * 🧠 IDEMPOTÊNCIA (evita envio duplicado)
         */
        if ($visita->status_sisab === 'enviado') {
            return;
        }

        /**
         * 🔒 bloqueio de concorrência
         */
        if ($visita->status_sisab === 'processando') {
            return;
        }

        try {
            /**
             * 📌 marca como processando
             */
            $visita->update([
                'status_sisab' => 'processando',
            ]);

            /**
             * 🚀 integração SISAB
             */
            $service->enviarParaSISAB($visita);

            /**
             * ✔ sucesso
             */
            $visita->update([
                'status_sisab' => 'enviado',
                'enviado_sisab_em' => now(),
                'erro_sisab' => null,
            ]);

        } catch (Throwable $e) {

            /**
             * ❌ erro controlado
             */
            $visita->update([
                'status_sisab' => 'erro',
                'erro_sisab' => $e->getMessage(),
            ]);

            Log::error('SISAB JOB FALHOU', [
                'visita_id' => $this->visitaId,
                'erro' => $e->getMessage(),
            ]);

            /**
             * 🔁 rethrow para retry automático do Laravel
             */
            throw $e;
        }
    }

    /**
     * 💀 falha definitiva após todas as tentativas
     */
    public function failed(Throwable $e): void
    {
        $visita = VisitaDomiciliar::find($this->visitaId);

        if ($visita) {
            $visita->update([
                'status_sisab' => 'falha_definitiva',
                'erro_sisab' => $e->getMessage(),
            ]);
        }

        Log::critical('SISAB JOB FALHA FINAL (SEM RETRY)', [
            'visita_id' => $this->visitaId,
            'erro' => $e->getMessage(),
        ]);
    }
}
