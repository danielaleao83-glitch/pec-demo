<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * 🔐 Permissão de acesso
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 📋 Regras de validação
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            /*
            |------------------------------------------------------------------
            | 👤 IDENTIFICAÇÃO
            |------------------------------------------------------------------
            */
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |------------------------------------------------------------------
            | 📧 E-MAIL (UNIQUE + SELF IGNORE)
            |------------------------------------------------------------------
            */
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }

    /**
     * 🔧 Normalização antes da validação
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email ? strtolower(trim($this->email)) : null,
            'name'  => $this->name ? trim($this->name) : null,
        ]);
    }

    /**
     * 🧠 Mensagens customizadas (padrão SUS-friendly)
     */
    public function messages(): array
    {
        return [
            'name.required'  => 'O nome é obrigatório.',
            'name.max'       => 'O nome deve ter no máximo 255 caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email'    => 'Informe um e-mail válido.',
            'email.unique'   => 'Este e-mail já está em uso.',
        ];
    }

    /**
     * 🔍 Validações adicionais (regra de negócio)
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            /*
            |--------------------------------------------------------------
            | 🚨 BLOQUEIO EXTRA: e-mail institucional SUS (exemplo)
            |--------------------------------------------------------------
            */

            if ($this->email && str_contains($this->email, '@sus.local')) {
                $validator->errors()->add(
                    'email',
                    'E-mails institucionais não podem ser alterados por este perfil.'
                );
            }
        });
    }
}