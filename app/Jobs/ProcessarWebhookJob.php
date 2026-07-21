<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessarWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $payload;

    public string $url;

    public int $tentativas = 0;

    public function __construct(string $url, array $payload)
    {
        $this->url = $url;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        try {
            $response = Http::timeout(10)
                ->retry(3, 1000)
                ->post($this->url, $this->payload);

            if (! $response->successful()) {
                throw new \Exception('Falha no webhook: '.$response->body());
            }

            Log::info('WEBHOOK ENVIADO COM SUCESSO', [
                'url' => $this->url,
                'payload' => $this->payload,
            ]);

        } catch (\Throwable $e) {

            Log::error('FALHA WEBHOOK', [
                'url' => $this->url,
                'erro' => $e->getMessage(),
                'tentativa' => $this->tentativas,
            ]);

            // retry automático do Laravel queue
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('WEBHOOK FALHOU DEFINITIVAMENTE', [
            'url' => $this->url,
            'erro' => $exception->getMessage(),
        ]);
    }
}
