<?php

namespace App\Services\ESusService\SUS;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SusHttpClient
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.sus.base_url');
        $this->token   = config('services.sus.token');
    }

    /*
    |--------------------------------------------------------------------------
    | 🌐 CLIENT BASE (RNDS / SUS FEDERAL READY)
    |--------------------------------------------------------------------------
    */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(20)
            ->retry(3, 500)
            ->withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',

                // 🔐 rastreabilidade federal
                'X-Request-ID'  => (string) Str::uuid(),
                'X-System'      => 'eSUS_APS_Laravel',
                'X-Origin'      => request()->ip() ?? 'CLI',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 📤 POST SEGURO (COM ID EMPOTÊNCIA + AUDITORIA)
    |--------------------------------------------------------------------------
    */
    public function post(string $endpoint, array $data)
    {
        $requestId = (string) Str::uuid();

        try {
            $payload = [
                'request_id' => $requestId,
                'timestamp'  => now()->toIso8601String(),
                'data'       => $data,
                'hash'       => $this->hash($data),
            ];

            $response = $this->client()
                ->withHeaders([
                    'X-Request-ID' => $requestId,
                ])
                ->post($endpoint, $payload);

            $this->logRequest('POST', $endpoint, $payload, $response->status(), $requestId);

            return $response;

        } catch (Throwable $e) {

            $this->logError('POST', $endpoint, $data, $e, $requestId);

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 📥 GET SEGURO (RASTREIO COMPLETO)
    |--------------------------------------------------------------------------
    */
    public function get(string $endpoint)
    {
        $requestId = (string) Str::uuid();

        try {
            $response = $this->client()
                ->withHeaders([
                    'X-Request-ID' => $requestId,
                ])
                ->get($endpoint);

            $this->logRequest('GET', $endpoint, [], $response->status(), $requestId);

            return $response;

        } catch (Throwable $e) {

            $this->logError('GET', $endpoint, [], $e, $requestId);

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🧾 LOG FEDERAL ESTRUTURADO (RNDS READY)
    |--------------------------------------------------------------------------
    */
    protected function logRequest(
        string $method,
        string $endpoint,
        array $data,
        int $status,
        string $requestId
    ): void {
        Log::info('SUS_HTTP_REQUEST', [
            'request_id' => $requestId,
            'method'     => $method,
            'endpoint'   => $endpoint,
            'status'     => $status,
            'user_id'    => auth()->id(),
            'ip'         => request()->ip() ?? 'CLI',
            'hash'       => $this->hash($data),
            'timestamp'  => now()->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🚨 LOG DE ERRO FEDERAL (COM CONTEXTO TOTAL)
    |--------------------------------------------------------------------------
    */
    protected function logError(
        string $method,
        string $endpoint,
        array $data,
        Throwable $e,
        string $requestId
    ): void {
        Log::error('SUS_HTTP_ERROR', [
            'request_id' => $requestId,
            'method'     => $method,
            'endpoint'   => $endpoint,
            'message'    => $e->getMessage(),
            'user_id'    => auth()->id(),
            'ip'         => request()->ip() ?? 'CLI',
            'hash'       => $this->hash($data),
            'timestamp'  => now()->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔐 HASH DE INTEGRIDADE (FEDERAL LEVEL)
    |--------------------------------------------------------------------------
    */
    protected function hash(array $data): string
    {
        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}