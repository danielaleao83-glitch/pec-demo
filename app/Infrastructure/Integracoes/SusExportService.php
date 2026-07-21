<?php

namespace App\Services\Integracoes;

use App\Models\Atendimento;
use App\Models\SusIntegracao;
use Illuminate\Support\Facades\Auth;

class SusExportService
{
    /**
     * Exporta um atendimento para o padrão SUS (simulado/local)
     */
    public function exportarAtendimento(Atendimento $atendimento): array
    {
        $payload = $this->montarPayload($atendimento);

        $log = SusIntegracao::create([
            'tipo' => 'atendimento_export',
            'endpoint' => 'ESUS_EXPORT_LOCAL',
            'payload' => $payload,
            'status_code' => 200,
            'resposta' => 'Exportação simulada com sucesso',
            'enviado_em' => now(),
        ]);

        return [
            'status' => true,
            'mensagem' => 'Atendimento exportado com sucesso',
            'log_id' => $log->id,
            'payload' => $payload,
        ];
    }

    /**
     * Lista exportações realizadas (logs)
     */
    public function listarLogs(int $limit = 50)
    {
        return SusIntegracao::latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Monta o payload padrão SUS
     */
    protected function montarPayload(Atendimento $atendimento): array
    {
        return [
            'atendimento_id' => $atendimento->id,

            'paciente' => [
                'id' => optional($atendimento->paciente)->id,
                'nome' => optional($atendimento->paciente)->nome,
                'cpf' => optional($atendimento->paciente)->cpf,
                'cns' => optional($atendimento->paciente)->cns,
                'data_nascimento' => optional($atendimento->paciente)->data_nascimento,
            ],

            'profissional' => [
                'id' => Auth::id(),
                'nome' => optional(Auth::user())->name,
            ],

            'unidade_id' => $atendimento->unidade_id,
            'data_atendimento' => $atendimento->created_at,

            'sistema' => 'eSUS-APS',
            'versao' => '1.0',
        ];
    }
}
