<?php

namespace App\Http\Requests\Evolucao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEvolucaoRequest extends FormRequest
{
    /**
     * 🔐 Segurança básica (somente usuários autenticados)
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 📥 Normalização de filtros (SUS padrão)
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'paciente_id' => $this->paciente_id ? (int) $this->paciente_id : null,
            'atendimento_id' => $this->atendimento_id ? (int) $this->atendimento_id : null,
            'profissional_id' => $this->profissional_id ? (int) $this->profissional_id : null,
            'tipo' => $this->tipo ? trim($this->tipo) : null,
            'data_inicio' => $this->data_inicio ?: null,
            'data_fim' => $this->data_fim ?: null,
        ]);
    }

    /**
     * 📋 Filtros permitidos (INDEX SUS)
     */
    public function rules(): array
    {
        return [
            /*
            |----------------------------------------------------------
            | 🧍 FILTROS PRINCIPAIS
            |----------------------------------------------------------
            */
            'paciente_id' => [
                'nullable',
                'integer',
                'exists:pacientes,id',
            ],

            'atendimento_id' => [
                'nullable',
                'integer',
                'exists:atendimentos,id',
            ],

            'profissional_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            /*
            |----------------------------------------------------------
            | 🏥 TIPO DE EVOLUÇÃO (CIAP/CLÍNICO)
            |----------------------------------------------------------
            */
            'tipo' => [
                'nullable',
                'string',
                Rule::in([
                    'clinica',
                    'administrativa',
                    'teleatendimento',
                    'educacional',
                ]),
            ],

            /*
            |----------------------------------------------------------
            | 📅 PERÍODO (SUS AUDITORIA)
            |----------------------------------------------------------
            */
            'data_inicio' => [
                'nullable',
                'date',
            ],

            'data_fim' => [
                'nullable',
                'date',
                'after_or_equal:data_inicio',
            ],

            /*
            |----------------------------------------------------------
            | 📊 PAGINAÇÃO (CONTROLE SISTEMA)
            |----------------------------------------------------------
            */
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    /**
     * 🧠 Regras adicionais de consistência SUS
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            /*
            |----------------------------------------------------------
            | 🔒 REGRA SUS: filtro deve ter pelo menos 1 critério
            |----------------------------------------------------------
            */
            if (
                !$this->paciente_id &&
                !$this->atendimento_id &&
                !$this->profissional_id &&
                !$this->tipo &&
                !$this->data_inicio &&
                !$this->data_fim
            ) {
                $validator->errors()->add(
                    'filtro',
                    'Informe pelo menos um critério de busca.'
                );
            }

            /*
            |----------------------------------------------------------
            | 📅 REGRA: intervalo máximo de 365 dias (auditoria SUS)
            |----------------------------------------------------------
            */
            if ($this->data_inicio && $this->data_fim) {

                $inicio = strtotime($this->data_inicio);
                $fim = strtotime($this->data_fim);

                $diffDias = ($fim - $inicio) / 86400;

                if ($diffDias > 365) {
                    $validator->errors()->add(
                        'data_fim',
                        'Intervalo máximo permitido é de 365 dias.'
                    );
                }
            }
        });
    }

    /**
     * 🧾 Mensagens padrão SUS
     */
    public function messages(): array
    {
        return [
            'paciente_id.exists' => 'Paciente não encontrado no sistema SUS.',
            'atendimento_id.exists' => 'Atendimento não encontrado.',
            'profissional_id.exists' => 'Profissional não encontrado.',

            'tipo.in' => 'Tipo de evolução inválido.',

            'data_fim.after_or_equal' => 'Data final deve ser maior ou igual à inicial.',

            'per_page.max' => 'Limite máximo de 100 registros por página.',
        ];
    }
}