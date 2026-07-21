<?php

declare(strict_types=1);

namespace App\Http\Requests\Territorial;

use Illuminate\Foundation\Http\FormRequest;

class TerritorializacaoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'municipio_id' => [
                'required',
                'integer',
            ],

            'equipe_id' => [
                'required',
                'integer',
            ],

            'microarea' => [
                'required',
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