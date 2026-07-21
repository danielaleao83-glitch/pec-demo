<?php

namespace App\Observers;

use Illuminate\Foundation\Http\FormRequest;

class AnexoClinicoObserver extends FormRequest
{
    /**
     * 🔐 Autorização da requisição
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * 📋 Regras de validação (SUS / produção)
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
                'in:clinica,administrativa,teleatendimento,educacional',
            ],

            'descricao' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * 🧠 Mensagens (opcional, mas bom para sistema clínico)
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

            'tipo.required' => 'O tipo de evolução é obrigatório.',
            'tipo.in' => 'Tipo de evolução inválido.',

            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição não pode ultrapassar 2000 caracteres.',
        ];
    }

    /**
     * 🧼 Sanitização (nível produção)
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'descricao' => trim($this->descricao ?? ''),
            'tipo' => strtolower($this->tipo ?? ''),
        ]);
    }
}