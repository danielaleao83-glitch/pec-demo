<?php

namespace App\Services\SUS;

use App\Models\Sus\SisabEnvio;
use Illuminate\Support\Str;
use Exception;

class SisabService
{
    protected SusHttpClient $http;

    public function __construct(SusHttpClient $http)
    {
        $this->http = $http;
    }

    /*
    |--------------------------------------------------------------------------
    | ENVIAR ATENDIMENTO PARA SISAB
    |--------------------------------------------------------------------------
    */

    public function enviarAtendimento(array $dados)
    {
        $envio = SisabEnvio::create([
            'uuid'        => (string) Str::uuid(),
            'tipo_envio'  => 'atendimento',
            'payload'     => $dados,
            'status'      => 'pendente',
        ]);

        try {

            $response = $this->http->post('/sisab/atendimento', $dados);

            if ($response->successful()) {

                $envio->update([
                    'status'       => 'enviado',
                    'enviado_em'   => now(),
                    'mensagem_retorno' => $response->body(),
                ]);

                return $envio;
            }

            throw new Exception($response->body());

        } catch (Exception $e) {

            $envio->update([
                'status' => 'erro',
                'mensagem_retorno' => $e->getMessage(),
                'tentativas' => $envio->tentativas + 1,
            ]);

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REENVIO AUTOMÁTICO (SUS ROBUSTO)
    |--------------------------------------------------------------------------
    */

    public function reenviarPendentes()
    {
        $pendentes = SisabEnvio::where('status', 'erro')
            ->where('tentativas', '<', 5)
            ->get();

        foreach ($pendentes as $envio) {
            try {
                $this->http->post('/sisab/atendimento', $envio->payload);

                $envio->update([
                    'status' => 'enviado',
                    'enviado_em' => now(),
                ]);

            } catch (Exception $e) {
                $envio->increment('tentativas');
            }
        }
    }
}