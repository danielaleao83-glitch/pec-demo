<?php

namespace App\Http\Requests\Evolucao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

 class StoreEvolucaoRequest extends FormRequest
{
    /**
     * 🔐 Autorização
     */
    public function authorize(): bool
    {
        // Ajuste depois com Policy se quiser
        return true;
    }

    /**
     * 📋 Regras de validação
     */
    public function rules(): array
    {
        return [
            'atendimento_id' => [
                'required',
                'integer',
                'exists:atendimentos,id',
            ],

            'paciente_id' => [
                'required',
                'integer',
                'exists:pacientes,id',
            ],

            'profissional_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'tipo' => [
                'required',
                'string',
                Rule::in([
                    'clinica',
                    'administrativa',
                    'teleatendimento',
                    'educacional',
                ]),
            ],

            'descricao' => [
                'required',
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
        $this->merge([
            'tipo' => strtolower(trim((string) $this->tipo)),
            'descricao' => trim((string) $this->descricao),
        ]);
    }

    /**
     * 🗣️ Mensagens customizadas
     */
    public function messages(): array
    {
        return [
            'atendimento_id.required' => 'O atendimento é obrigatório.',
            'atendimento_id.exists' => 'Atendimento inválido.',

            'paciente_id.required' => 'O paciente é obrigatório.',
            'paciente_id.exists' => 'Paciente inválido.',

            'profissional_id.required' => 'O profissional é obrigatório.',
            'profissional_id.exists' => 'Profissional inválido.',

            'tipo.required' => 'O tipo da evolução é obrigatório.',
            'tipo.in' => 'Tipo de evolução inválido.',

            'descricao.required' => 'A descrição é obrigatória.',
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