<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 20;

    protected string $messageId;

    public function __construct(string $messageId)
    {
        $this->messageId = $messageId;
    }

    /*
    |----------------------------------------------------------------------
    | 🔒 LOCK ANTI DUPLICIDADE
    |----------------------------------------------------------------------
    */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->messageId))->expireAfter(60),
        ];
    }

    /*
    |----------------------------------------------------------------------
    | ⏱ BACKOFF NATIVO (SEGUNDOS)
    |----------------------------------------------------------------------
    */
    public function backoff(): array
    {
        return [10, 30, 60, 120, 300];
    }

    /*
    |----------------------------------------------------------------------
    | 🚀 EXECUÇÃO
    |----------------------------------------------------------------------
    */
    public function handle(WhatsAppService $service): void
    {
        $message = WhatsappMessage::find($this->messageId);

        if (! $message) {
            Log::warning('WhatsAppJob: mensagem não encontrada', [
                'id' => $this->messageId,
            ]);

            return;
        }

        // evita reprocessar
        if ($message->status === 'sent') {
            return;
        }

        try {
            $message->markAsProcessing();

            $result = $service->enviar(
                $message->phone,
                $message->message,
                $message->user_id
            );

            if ($result['status'] === 'sent') {

                $message->markAsSent($result['response'] ?? []);

                Log::info('WhatsApp enviado', [
                    'message_id' => $message->id,
                ]);

                return;
            }

            throw new \Exception($result['message'] ?? 'Erro desconhecido');
        } catch (\Throwable $e) {

            Log::error('Erro envio WhatsApp', [
                'message_id' => $message->id,
                'erro' => $e->getMessage(),
                'tentativa' => $this->attempts(),
            ]);

            // marca falha parcial (para monitoramento)
            $message->markAsFailed($e->getMessage());

            // ❗ MUITO IMPORTANTE:
            // deixa o Laravel cuidar do retry automático
            throw $e;
        }
    }

    /*
    |----------------------------------------------------------------------
    | ❌ FALHA FINAL (DEPOIS DE TODAS TENTATIVAS)
    |----------------------------------------------------------------------
    */
    public function failed(\Throwable $exception): void
    {
        Log::critical('WhatsApp FALHA DEFINITIVA', [
            'message_id' => $this->messageId,
            'erro' => $exception->getMessage(),
        ]);

        $message = WhatsappMessage::find($this->messageId);

        if ($message) {
            $message->update([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
