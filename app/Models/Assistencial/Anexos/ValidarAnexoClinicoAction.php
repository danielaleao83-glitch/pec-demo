<?php

namespace App\Models\Assistencial\Anexos;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class ValidarAnexoClinicoAction
{
    /**
     * Executa validação completa do Anexo Clínico
     * padrão hospitalar (e-SUS-like, mas privado e seguro)
     */
    public function execute(array $data): array
    {
        $validator = Validator::make($data, [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'tipo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:2000',
            'arquivo' => 'nullable|string|max:255',
            'assinatura_medico' => 'nullable|string|max:255',
        ], [
            'paciente_id.required' => 'Paciente é obrigatório.',
            'atendimento_id.required' => 'Atendimento é obrigatório.',
            'tipo.required' => 'Tipo do anexo é obrigatório.',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException(
                $validator->errors()->first()
            );
        }

        return $this->sanitizar($validator->validated());
    }

    /**
     * Sanitização de segurança (anti-XSS básico hospitalar)
     */
    private function sanitizar(array $data): array
    {
        return [
            'paciente_id' => (int) $data['paciente_id'],
            'atendimento_id' => (int) $data['atendimento_id'],
            'tipo' => strip_tags($data['tipo']),
            'descricao' => isset($data['descricao']) ? strip_tags($data['descricao']) : null,
            'arquivo' => $data['arquivo'] ?? null,
            'assinatura_medico' => $data['assinatura_medico'] ?? null,
        ];
    }
}