<?php

declare(strict_types=1);

namespace App\Application\Services\Paciente\CriarPaciente;

use Illuminate\Support\Facades\Validator;

final class CriarPacienteValidator
{
    public function validate(
        array $payload
    ): void {

        Validator::make(
            $payload,
            [

                'nome' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'cpf' => [
                    'required',
                    'digits:11',
                ],

                'cns' => [
                    'required',
                    'digits:15',
                ],

                'data_nascimento' => [
                    'required',
                    'date',
                ],
            ]
        )->validate();
    }
}