<?php

namespace App\Services\Integracoes;

use App\Models\Producao\ProducaoAmbulatorial;

class BPAOficialService
{
    /**
     * 🔥 Gera arquivo BPA padrão DATASUS (simplificado oficial)
     */
    public function gerarArquivo(ProducaoAmbulatorial $producao): string
    {
        $linhas = [];

        foreach ($producao->itens()->with(['procedimento', 'atendimento.paciente', 'atendimento.profissional'])->get() as $item) {

            $atendimento = $item->atendimento;
            $paciente = $atendimento->paciente;
            $profissional = $atendimento->profissional;

            // CNES (unidade)
            $cnes = str_pad($producao->unidade->cnes ?? '0000000', 7, '0', STR_PAD_LEFT);

            // CNS paciente
            $cns = str_pad(preg_replace('/\D/', '', $paciente->cns ?? ''), 15, '0', STR_PAD_LEFT);

            // CBO profissional
            $cbo = str_pad(preg_replace('/\D/', '', $profissional->cbo ?? ''), 6, '0', STR_PAD_LEFT);

            // Procedimento SIGTAP
            $procedimento = str_pad($item->procedimento->codigo ?? '0000000000', 10, '0', STR_PAD_LEFT);

            // Data
            $data = date('Ymd', strtotime($item->data_execucao));

            // Quantidade
            $quantidade = str_pad($item->quantidade, 3, '0', STR_PAD_LEFT);

            // Linha padrão BPA (estrutura fixa simplificada)
            $linha =
                $cnes.
                $cns.
                $cbo.
                $procedimento.
                $quantidade.
                $data;

            $linhas[] = $linha;
        }

        return implode("\n", $linhas);
    }
}
