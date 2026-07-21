<?php

namespace App\Http\Requests\Clinico;

use Illuminate\Foundation\Http\FormRequest;

class StoreSinalVitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required','exists:pacientes,id'],
            'atendimento_id' => ['required','exists:atendimentos,id'],
            'data_registro' => ['required','date','before_or_equal:now'],

            'peso' => ['nullable','numeric','between:0,500'],
            'altura' => ['nullable','numeric','between:0,3'],
            'pa' => ['nullable','string','max:20'],
            'fc' => ['nullable','integer','between:0,250'],
            'temperatura' => ['nullable','numeric','between:30,45'],
        ];
    }
}