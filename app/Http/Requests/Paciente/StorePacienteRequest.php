<?php

namespace App\Http\Requests\Paciente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Paciente\Paciente;

class StorePacienteRequest extends FormRequest
{
    /**
     * 🔐 Autorização via Policy (padrão SUS seguro)
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Paciente::class) ?? false;
    }

    /**
     * 🧼 Normalização de entrada (LGPD + SUS padrão)
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'paciente_id' => $this->paciente_id ? (int) $this->paciente_id : null,
            'profissional_id' => $this->user()?->id,
            'tipo' => $this->tipo ? trim($this->tipo) : null,
        ]);
    }

    /**
     * 📋 Regras de validação (nível e-SUS)
     */
    public function rules(): array
    {
        return [
            /*
            |----------------------------------------------------------
            | 🧍 PACIENTE
            |----------------------------------------------------------
            */
            'paciente_id' => [
                'required',
                'integer',
                'exists:pacientes,id',
            ],

            /*
            |----------------------------------------------------------
            | 👨‍⚕️ PROFISSIONAL
            |----------------------------------------------------------
            */
            'profissional_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            /*
            |----------------------------------------------------------
            | 🏥 TIPO DE ATENDIMENTO SUS
            |----------------------------------------------------------
            */
            'tipo' => [
                'required',
                'string',
                Rule::in([
                    'consulta',
                    'retorno',
                    'urgencia',
                    'visita_domiciliar',
                    'procedimento',
                    'saude_mental',
                ]),
            ],

            /*
            |----------------------------------------------------------
            | 📝 EVOLUÇÃO CLÍNICA
            |----------------------------------------------------------
            */
            'descricao' => [
                'required',
                'string',
                'max:5000',
            ],

            /*
            |----------------------------------------------------------
            | 🔁 RETORNO (OPCIONAL - NÃO É MODEL DIRETO NO REQUEST)
            |----------------------------------------------------------
            */
            'retorno' => [
                'nullable',
                'array',
            ],

            'retorno.data_retorno' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            'retorno.motivo' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * 🧠 Regras de negócio leves (sem domínio aqui)
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            /*
            |----------------------------------------------------------
            | 🚨 REGRA SUS: retorno exige motivo
            |----------------------------------------------------------
            */
            if ($this->has('retorno') && !empty($this->retorno)) {

                if (empty($this->retorno['motivo'] ?? null)) {
                    $validator->errors()->add(
                        'retorno.motivo',
                        'O motivo do retorno é obrigatório segundo regra SUS.'
                    );
                }
            }

            /*
            |----------------------------------------------------------
            | 🔒 REGRA SEGURANÇA: paciente obrigatório
            |----------------------------------------------------------
            */
            if (!$this->paciente_id) {
                $validator->errors()->add(
                    'paciente_id',
                    'Paciente é obrigatório para atendimento SUS.'
                );
            }
        });
    }

    /**
     * 📌 Mensagens padronizadas SUS
     */
    public function messages(): array
    {
        return [
            'paciente_id.required' => 'Paciente obrigatório.',
            'paciente_id.exists'   => 'Paciente não encontrado no sistema SUS.',

            'tipo.in' => 'Tipo de atendimento inválido para padrão e-SUS.',

            'descricao.required' => 'Descrição clínica é obrigatória.',
            'descricao.max'      => 'Descrição excede limite de 5000 caracteres.',
        ];
    }
}