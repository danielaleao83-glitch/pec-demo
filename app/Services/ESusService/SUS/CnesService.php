<?php

namespace App\Services\ESusService\SUS;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CnesService
{
    protected SusHttpClient $http;

    public function __construct(SusHttpClient $http)
    {
        $this->http = $http;
    }

    /*
    |--------------------------------------------------------------------------
    | 🔐 CONSULTA CNES (COM RASTREIO FEDERAL)
    |--------------------------------------------------------------------------
    */
    public function consultarUnidade(string $cnes): ?array
    {
        try {
            $cnes = $this->normalizarCnes($cnes);

            if (! $this->validarFormato($cnes)) {
                throw new \InvalidArgumentException('CNES inválido');
            }

            $response = $this->http
                ->timeout(10)
                ->retry(2, 200)
                ->get("/cnes/unidade/{$cnes}");

            $data = $response->json();

            Log::info('CNES_CONSULTA', [
                'cnes' => $cnes,
                'trace_id' => (string) Str::uuid(),
                'status_http' => $response->status(),
            ]);

            return $data;

        } catch (\Throwable $e) {

            Log::error('CNES_CONSULTA_ERRO', [
                'cnes' => $cnes ?? null,
                'erro' => $e->getMessage(),
                'trace_id' => (string) Str::uuid(),
            ]);

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🏥 UNIDADE ATIVA (VALIDAÇÃO SEGURA)
    |--------------------------------------------------------------------------
    */
    public function unidadeAtiva(string $cnes): bool
    {
        $data = $this->consultarUnidade($cnes);

        if (! $data) {
            return false;
        }

        return (bool) ($data['status'] ?? false);
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 NORMALIZAÇÃO SUS
    |--------------------------------------------------------------------------
    */
    protected function normalizarCnes(string $cnes): string
    {
        return preg_replace('/\D/', '', $cnes);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔐 VALIDAÇÃO FORMAL CNES (7 dígitos SUS)
    |--------------------------------------------------------------------------
    */
    protected function validarFormato(string $cnes): bool
    {
        return preg_match('/^\d{7}$/', $cnes) === 1;
    }

    /*
    |--------------------------------------------------------------------------
    | 📊 HEALTH CHECK CNES (nível governamental)
    |--------------------------------------------------------------------------
    */
    public function healthCheck(string $cnes): array
    {
        $data = $this->consultarUnidade($cnes);

        return [
            'cnes' => $cnes,
            'ativo' => (bool) ($data['status'] ?? false),
            'valido' => $this->validarFormato($cnes),
            'integracao_ok' => $data !== null,
        ];
    }
}