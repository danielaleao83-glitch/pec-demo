<?php

namespace App\Services\Atendimento;

use App\Models\Triagem;
use App\Models\Atendimento;
use Illuminate\Support\Facades\DB;

class TriagemService
{
    /**
     * 🧠 TRIAGEM CLÍNICA REAL (classificação de risco)
     */
    public function realizarTriagem(array $data, $profissionalId)
    {
        return DB::transaction(function () use ($data, $profissionalId) {

            /**
             * 📊 Regra simples de risco (pode evoluir para Manchester real)
             */
            $risco = $this->calcularRisco($data);

            $triagem = Triagem::create([
                'atendimento_id' => $data['atendimento_id'],
                'profissional_id' => $profissionalId,

                'pressao_arterial' => $data['pressao_arterial'] ?? null,
                'temperatura' => $data['temperatura'] ?? null,
                'frequencia_cardiaca' => $data['frequencia_cardiaca'] ?? null,
                'sintomas' => $data['sintomas'] ?? null,
                'queixa_principal' => $data['queixa_principal'],

                'risco' => $risco,
            ]);

            /**
             * 🔄 Atualiza atendimento automaticamente (fluxo SUS real)
             */
            Atendimento::where('id', $data['atendimento_id'])
                ->update([
                    'prioridade' => $this->mapearPrioridade($risco),
                    'status' => 'triado',
                    'ultimo_evento' => 'TRIAGEM_REALIZADA',
                ]);

            return $triagem;
        });
    }

    /**
     * 🚦 LÓGICA DE RISCO (base inicial)
     */
    private function calcularRisco(array $data): string
    {
        if (
            isset($data['frequencia_cardiaca']) && $data['frequencia_cardiaca'] > 120 ||
            isset($data['temperatura']) && $data['temperatura'] > 39
        ) {
            return 'vermelho';
        }

        return 'verde';
    }

    /**
     * 📊 CONVERSÃO PARA FILA SUS
     */
    private function mapearPrioridade(string $risco): int
    {
        return match ($risco) {
            'vermelho' => 3,
            'amarelo' => 2,
            default => 1,
        };
    }
}