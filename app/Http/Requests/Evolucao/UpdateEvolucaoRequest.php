<?php

namespace App\Http\Requests\Evolucao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvolucaoRequest extends FormRequest
{
    /**
     * 🔐 Autorização
     */
    public function authorize(): bool
    {
        // Depois você pode integrar com Policy (ex: $this->user()->can('update', $this->route('evolucao')))
        return true;
    }

    /**
     * 📋 Regras de validação
     */
    public function rules(): array
    {
        return [
            'atendimento_id' => [
                'sometimes',
                'integer',
                'exists:atendimentos,id',
            ],

            'paciente_id' => [
                'sometimes',
                'integer',
                'exists:pacientes,id',
            ],

            'profissional_id' => [
                'sometimes',
                'integer',
                'exists:users,id',
            ],

            'tipo' => [
                'sometimes',
                'string',
                Rule::in([
                    'clinica',
                    'administrativa',
                    'teleatendimento',
                    'educacional',
                ]),
            ],

            'descricao' => [
                'sometimes',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * 🧠 Normalização antes da validação
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('tipo')) {
            $data['tipo'] = strtolower(trim((string) $this->tipo));
        }

        if ($this->has('descricao')) {
            $data['descricao'] = trim((string) $this->descricao);
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * 🗣️ Mensagens customizadas
     */
    public function messages(): array
    {
        return [
            'atendimento_id.exists' => 'Atendimento inválido.',
            'paciente_id.exists' => 'Paciente inválido.',
            'profissional_id.exists' => 'Profissional inválido.',

            'tipo.in' => 'Tipo de evolução inválido.',

            'descricao.max' => 'A descrição deve ter no máximo 2000 caracteres.',
        ];
    }

    /**
     * 🏷️ Nomes amigáveis
     */
    public function attributes(): array
    {
        return [
            'atendimento_id' => 'atendimento',
            'paciente_id' => 'paciente',
            'profissional_id' => 'profissional',
            'tipo' => 'tipo de evolução',
            'descricao' => 'descrição',
        ];
    }
}