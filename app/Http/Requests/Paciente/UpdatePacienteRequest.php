<?php

namespace App\Http\Requests\Paciente;

use App\Services\SUS\ValidacaoSUSService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $paciente = $this->route('paciente');

        return $this->user()->can('update', $paciente);
    }

    public function prepareForValidation(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 🔧 NORMALIZAÇÃO (REMOVE MÁSCARAS)
        |--------------------------------------------------------------------------
        */
        $this->merge([
            'cpf' => $this->cpf ? preg_replace('/\D/', '', $this->cpf) : null,
            'cns' => $this->cns ? preg_replace('/\D/', '', $this->cns) : null,
            'telefone' => $this->telefone ? preg_replace('/\D/', '', $this->telefone) : null,
        ]);
    }

    public function rules(): array
    {
        $paciente = $this->route('paciente');
        $pacienteId = $paciente->id ?? null;

        return [
            /*
            |--------------------------------------------------------------------------
            | 🧾 DADOS PRINCIPAIS
            |--------------------------------------------------------------------------
            */
            'nome' => 'required|string|max:255',

            /*
            |--------------------------------------------------------------------------
            | 🆔 DOCUMENTOS SUS
            |--------------------------------------------------------------------------
            */
            'cpf' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('pacientes', 'cpf')->ignore($pacienteId),
            ],

            'cns' => 'nullable|string|size:15',

            /*
            |--------------------------------------------------------------------------
            | 📅 DADOS PESSOAIS
            |--------------------------------------------------------------------------
            */
            'data_nascimento' => 'nullable|date|before:today',
            'nome_mae' => 'nullable|string|max:255',

            /*
            |--------------------------------------------------------------------------
            | 📞 CONTATO
            |--------------------------------------------------------------------------
            */
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',

            /*
            |--------------------------------------------------------------------------
            | 🚨 PRIORIDADE SUS
            |--------------------------------------------------------------------------
            */
            'prioridade' => 'nullable|integer|in:0,1,2,3',
            'prioridade_motivo' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do paciente é obrigatório.',

            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado.',

            'cns.size' => 'O CNS deve ter exatamente 15 dígitos.',

            'data_nascimento.before' => 'A data de nascimento deve ser no passado.',

            'prioridade.in' => 'Prioridade inválida.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $sus = app(ValidacaoSUSService::class);

            /*
            |--------------------------------------------------------------------------
            | 🔍 VALIDAÇÃO REAL SUS
            |--------------------------------------------------------------------------
            */

            if ($this->cpf && ! $sus->validarCPF($this->cpf)['valido']) {
                $validator->errors()->add('cpf', 'CPF inválido');
            }

            if ($this->cns && ! $sus->validarCNS($this->cns)['valido']) {
                $validator->errors()->add('cns', 'CNS inválido');
            }

            /*
            |--------------------------------------------------------------------------
            | 🚨 REGRA SUS - PRIORIDADE EXIGE MOTIVO
            |--------------------------------------------------------------------------
            */

            if ($this->prioridade && $this->prioridade > 0 && empty($this->prioridade_motivo)) {
                $validator->errors()->add('prioridade_motivo', 'Informe o motivo da prioridade.');
            }

            /*
            |--------------------------------------------------------------------------
            | 🔒 LGPD - IDENTIFICAÇÃO OBRIGATÓRIA
            |--------------------------------------------------------------------------
            */

            if (! $this->cpf && ! $this->cns) {
                $validator->errors()->add('cpf', 'Informe CPF ou CNS do paciente.');
            }

        });
    }
}
