<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGateway
{
    protected string $baseUrl;
    protected string $token;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.whatsapp.url'), '/');
        $this->token = config('services.whatsapp.token');
        $this->timeout = config('services.whatsapp.timeout', 10);
    }

    /**
     * 📤 Envia mensagem WhatsApp
     */
    public function enviar(string $numero, string $mensagem): array
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout($this->timeout)
                ->retry(3, 200) // 🔥 retry automático
                ->post("{$this->baseUrl}/send", [
                    'numero' => $numero,
                    'mensagem' => $mensagem,
                ]);

            if ($response->failed()) {

                Log::error('WHATSAPP_ERRO_RESPOSTA', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Falha na API do WhatsApp');
            }

            return $response->json() ?? [];

        } catch (\Throwable $e) {

            Log::error('WHATSAPP_GATEWAY_EXCEPTION', [
                'erro' => $e->getMessage(),
                'numero' => $numero,
            ]);

            throw $e;
        }
    }

    /**
     * 📡 Webhook
     */
    public function webhook(array $payload): array
    {
        Log::info('WHATSAPP_WEBHOOK_RECEBIDO', $payload);

        return ['status' => 'ok'];
    }

    /**
     * 🔐 Validação do webhook
     */
    public function validarWebhook($request): bool
    {
        $signature = $request->header(
            config('services.security.signature_header', 'X-SIGNATURE')
        );

        $secret = config('services.whatsapp.webhook_secret');

        if (!$signature || !$secret) {
            return false;
        }

        return hash_equals($secret, $signature);
    }
}