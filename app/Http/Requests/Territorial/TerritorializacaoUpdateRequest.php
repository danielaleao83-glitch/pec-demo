<?php

declare(strict_types=1);

namespace App\Http\Requests\Territorial;

use Illuminate\Foundation\Http\FormRequest;

class TerritorializacaoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'microarea' => [
                'sometimes',
                'string',
                'max:50',
            ],

            'descricao' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'geo_json' => [
                'nullable',
                'array',
            ],
        ];
    }
}