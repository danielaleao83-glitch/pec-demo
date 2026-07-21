<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\Web;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
        ];
    }
}