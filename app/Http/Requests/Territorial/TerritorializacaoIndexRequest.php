<?php

declare(strict_types=1);

namespace App\Http\Requests\Territorial;

use Illuminate\Foundation\Http\FormRequest;

class TerritorializacaoIndexRequest extends FormRequest
{
    private const MAX_PER_PAGE = 100;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'municipio_id' => [
                'nullable',
                'integer',
            ],

            'equipe_id' => [
                'nullable',
                'integer',
            ],

            'microarea' => [
                'nullable',
                'string',
                'max:50',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . self::MAX_PER_PAGE,
            ],
        ];
    }
}