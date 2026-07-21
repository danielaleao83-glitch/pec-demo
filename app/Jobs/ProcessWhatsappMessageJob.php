<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public string $messageId) {}

    public function handle(WhatsAppService $service)
    {
        $msg = WhatsappMessage::find($this->messageId);

        if (! $msg) {
            return;
        }

        try {
            $msg->update(['status' => 'processing']);

            $result = $service->enviar(
                $msg->phone,
                $msg->message,
                $msg->user_id
            );

            if ($result['status'] === 'sent') {

                $msg->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response' => $result['response'] ?? null,
                ]);

                return;
            }

            throw new \Exception($result['message'] ?? 'Erro envio');
        } catch (\Throwable $e) {

            $msg->increment('attempts');

            // 🔁 RETRY INTELIGENTE
            if ($msg->attempts < $msg->max_attempts) {

                $delay = $this->calculateDelay($msg->attempts);

                $msg->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'next_retry_at' => now()->addSeconds($delay),
                ]);

                self::dispatch($msg->id)->delay($delay);

                return;
            }

            // ❌ FALHA DEFINITIVA
            $msg->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /*
    |----------------------------------------------------------
    | ⏱ RETRY EXPONENCIAL SUS
    |----------------------------------------------------------
    */
    private function calculateDelay(int $attempts): int
    {
        return match ($attempts) {
            1 => 60,      // 1 min
            2 => 300,     // 5 min
            3 => 900,     // 15 min
            4 => 3600,    // 1h
            default => 7200
        };
    }
}
