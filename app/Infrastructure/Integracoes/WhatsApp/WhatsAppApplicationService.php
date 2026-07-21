<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\Paciente\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class WhatsAppApplicationService
{
    private const LIMIT = 10;

    public function __construct(
        private readonly WhatsAppDomainService $domain
    ) {}

    public function sendManual(Request $request): array
    {
        $data = $request->validate([
            'numero' => ['required', 'string'],
            'mensagem' => ['required', 'string', 'max:1000'],
        ]);

        $this->rateLimit($request);

        $numero = $this->normalize($data['numero']);

        return $this->domain->send(
            $numero,
            trim($data['mensagem']),
            Auth::id()
        );
    }

    public function sendToPatient(Request $request, string $id): array
    {
        $this->rateLimit($request);

        $paciente = Paciente::findOrFail($id);

        if (empty($paciente->telefone)) {
            return [
                'status' => 'error',
                'message' => 'Paciente sem telefone',
            ];
        }

        $mensagem = $request->input('mensagem')
            ?? $this->domain->getTemplate(
                $request->input('template', 'default'),
                $paciente
            );

        return $this->domain->sendToPatient(
            $paciente,
            trim($mensagem),
            Auth::id()
        );
    }

    public function handleWebhook(Request $request): array
    {
        if (! $this->domain->validateWebhook($request)) {
            return ['status' => 'unauthorized'];
        }

        return $this->domain->processWebhook($request);
    }

    private function rateLimit(Request $request): void
    {
        $key = 'whatsapp:' . Auth::id() . ':' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::LIMIT)) {
            abort(429, 'Limite excedido');
        }

        RateLimiter::hit($key, 60);
    }

    private function normalize(string $number): string
    {
        $number = preg_replace('/\D+/', '', $number);

        return str_starts_with($number, '55')
            ? '+' . $number
            : '+55' . $number;
    }
}