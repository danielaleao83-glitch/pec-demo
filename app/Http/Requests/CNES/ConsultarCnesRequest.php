<?php

declare(strict_types=1);

namespace App\Http\Requests\CNES;

use Illuminate\Foundation\Http\FormRequest;

final class ConsultarCnesRequest
    extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'cnes' => [
                'nullable',
                'regex:/^[0-9]{7}$/',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([

            'cnes' => $this->route('cnes'),
        ]);
    }

    public function messages(): array
    {
        return [

            'cnes.regex'
                => 'CNES inválido.',
        ];
    }
}