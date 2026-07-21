<?php

namespace App\Services;

use App\Models\Paciente\Paciente;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppService
{
    protected string $baseUrl;

    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.url');
        $this->token = config('services.whatsapp.token');
    }

    /*
    |--------------------------------------------------------------------------
    | 📤 ENVIO DIRETO (COM ID EMPOTÊNCIA)
    |--------------------------------------------------------------------------
    */
    public function enviar(
        string $numero,
        string $mensagem,
        ?int $userId = null,
        ?string $idempotencyKey = null
    ): array {
        $idempotencyKey ??= (string) Str::uuid();

        try {
            $payload = [
                'to' => $this->formatarNumero($numero),
                'message' => $mensagem,
                'idempotency_key' => $idempotencyKey,
            ];

            $response = Http::withToken($this->token)
                ->timeout(8)
                ->retry(3, 200)
                ->post($this->baseUrl.'/send', $payload);

            if ($response->failed()) {
                throw new \Exception($response->body());
            }

            Log::info('WhatsApp enviado', [
                'user_id' => $userId,
                'numero' => $numero,
                'idempotency' => $idempotencyKey,
            ]);

            return [
                'status' => 'sent',
                'idempotency_key' => $idempotencyKey,
                'response' => $response->json(),
            ];

        } catch (\Throwable $e) {

            Log::error('WhatsApp envio falhou', [
                'erro' => $e->getMessage(),
                'numero' => $numero,
                'user_id' => $userId,
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'idempotency_key' => $idempotencyKey,
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 ENVIO PARA PACIENTE (PADRÃO SUS)
    |--------------------------------------------------------------------------
    */
    public function enviarPaciente(
        Paciente $paciente,
        ?string $mensagem = null,
        ?int $userId = null,
        ?string $template = 'default'
    ): array {
        $numero = $paciente->telefone;

        if (! $numero) {
            return [
                'status' => 'error',
                'message' => 'Paciente sem telefone',
            ];
        }

        $mensagem ??= $this->getTemplate($template, $paciente);

        return $this->enviar(
            $numero,
            $mensagem,
            $userId,
            (string) Str::uuid()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 TEMPLATE SYSTEM (ESCALÁVEL SUS)
    |--------------------------------------------------------------------------
    */
    public function getTemplate(string $template, Paciente $paciente): string
    {
        return match ($template) {

            'consulta_confirmada' => "Olá {$paciente->nome}, sua consulta foi confirmada no SUS.",

            'lembrete_consulta' => "Lembrete: {$paciente->nome}, você tem consulta agendada.",

            'triagem' => "Olá {$paciente->nome}, dirija-se à triagem da unidade.",

            'vacina' => "Olá {$paciente->nome}, atualização de vacinação disponível.",

            default => "Olá {$paciente->nome}, mensagem do sistema de saúde.",
        };
    }

    /*
    |--------------------------------------------------------------------------
    | 📡 WEBHOOK (SEGURANÇA REAL)
    |--------------------------------------------------------------------------
    */
    public function webhook($request): array
    {
        if (! $this->validarWebhook($request)) {
            Log::warning('Webhook WhatsApp inválido', [
                'ip' => $request->ip(),
            ]);

            return [
                'status' => 'unauthorized',
            ];
        }

        $data = $request->all();

        Log::info('Webhook WhatsApp', [
            'payload' => $data,
        ]);

        return [
            'status' => 'received',
            'data' => $data,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 🔐 VALIDAÇÃO WEBHOOK
    |--------------------------------------------------------------------------
    */
    public function validarWebhook($request): bool
    {
        return hash_equals(
            config('services.whatsapp.webhook_token'),
            (string) $request->header('X-Webhook-Token')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 📞 NORMALIZAÇÃO TELEFONE (BRASIL SUS)
    |--------------------------------------------------------------------------
    */
    private function formatarNumero(string $numero): string
    {
        $numero = preg_replace('/\D/', '', $numero);

        if (! str_starts_with($numero, '55')) {
            $numero = '55'.$numero;
        }

        return $numero;
    }
}
