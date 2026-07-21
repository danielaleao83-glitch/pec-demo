<?php

namespace App\Jobs;

use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(
        public string $messageId
    ) {}

    public function handle(WhatsAppGateway $gateway): void
    {
        $msg = WhatsAppMessage::find($this->messageId);

        if (! $msg) {
            return;
        }

        // 🔒 evita reprocessamento duplicado
        if ($msg->status === 'sent') {
            return;
        }

        try {
            $msg->update([
                'status' => 'processing',
                'attempts' => $msg->attempts + 1,
            ]);

            // 📤 envio via provider (Twilio / Z-API / etc)
            $response = $gateway->send(
                $msg->phone,
                $msg->message
            );

            $msg->update([
                'status' => 'sent',
                'response' => $response,
            ]);

        } catch (\Throwable $e) {

            Log::error('WhatsAppJob failed', [
                'message_id' => $msg->id,
                'error' => $e->getMessage(),
            ]);

            $attempts = $msg->attempts;

            $msg->update([
                'status' => $attempts >= $msg->max_attempts ? 'failed' : 'pending',
                'error' => $e->getMessage(),
                'next_retry_at' => now()->addMinutes(pow(2, $attempts)),
            ]);

            if ($attempts >= $msg->max_attempts) {
                return;
            }

            // 🔁 reencaminha automaticamente
            $this->release(10 * $attempts);
        }
    }
}
