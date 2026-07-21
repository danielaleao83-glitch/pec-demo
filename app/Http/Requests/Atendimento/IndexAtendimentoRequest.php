<?php

namespace App\Http\Requests\Atendimento;

use Illuminate\Foundation\Http\FormRequest;

class IndexAtendimentoRequest extends FormRequest
{
    /**
     * 🔐 Controle de acesso (LGPD + perfil profissional)
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', \App\Models\Atendimento\Atendimento::class);
    }

    /**
     * 📌 Query filters (index padrão SUS)
     */
    public function rules(): array
    {
        return [
            /*
            |----------------------------------------
            | 👤 PACIENTE
            |----------------------------------------
            */
            'paciente_id' => ['nullable', 'integer', 'exists:pacientes,id'],

            /*
            |----------------------------------------
            | 🏥 PROFISSIONAL
            |----------------------------------------
            */
            'profissional_id' => ['nullable', 'integer', 'exists:users,id'],

            /*
            |----------------------------------------
            | 📅 PERÍODO (SUS padrão auditoria)
            |----------------------------------------
            */
            'inicio' => ['nullable', 'date'],
            'fim' => ['nullable', 'date', 'after_or_equal:inicio'],

            /*
            |----------------------------------------
            | 📊 STATUS CLÍNICO
            |----------------------------------------
            */
            'status' => ['nullable', 'string', 'in:aberto,finalizado,cancelado,pendente'],

            /*
            |----------------------------------------
            | 🧠 TIPO DE ATENDIMENTO
            |----------------------------------------
            */
            'tipo' => ['nullable', 'string', 'in:medico,enfermagem,psicologico,social'],

            /*
            |----------------------------------------
            | 🔎 BUSCA LIVRE (SUS-like search)
            |----------------------------------------
            */
            'search' => ['nullable', 'string', 'max:255'],

            /*
            |----------------------------------------
            | 📄 PAGINAÇÃO CONTROLADA
            |----------------------------------------
            */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],

            /*
            |----------------------------------------
            | 📌 ORDENAÇÃO SEGURA
            |----------------------------------------
            */
            'sort' => ['nullable', 'string', 'in:created_at,updated_at,data_atendimento'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    /**
     * 🔧 Normalização de entrada (nível SUS)
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'paciente_id' => $this->paciente_id ? (int) $this->paciente_id : null,
            'profissional_id' => $this->profissional_id ? (int) $this->profissional_id : null,
            'per_page' => $this->per_page ? (int) $this->per_page : 15,
            'sort' => $this->sort ?? 'created_at',
            'direction' => $this->direction ?? 'desc',
        ]);
    }

    /**
     * 🧠 Mensagens claras (nível sistema federal)
     */
    public function messages(): array
    {
        return [
            'fim.after_or_equal' => 'A data final deve ser maior ou igual à inicial.',
            'status.in' => 'Status inválido para atendimento SUS.',
            'tipo.in' => 'Tipo de atendimento não reconhecido pelo sistema.',
            'per_page.max' => 'Limite máximo de registros por página excedido.',
        ];
    }
}