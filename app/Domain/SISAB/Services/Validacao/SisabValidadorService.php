<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Validacao;

use Illuminate\Support\Facades\Validator;
use RuntimeException;

class SisabValidadorService
{
    public static function validar(
        array $dados
    ): void {

        $validator = Validator::make(
            $dados,
            [

                'paciente_uuid' => [
                    'required',
                    'uuid',
                ],

                'profissional_uuid' => [
                    'required',
                    'uuid',
                ],

                'unidade_uuid' => [
                    'nullable',
                    'uuid',
                ],

                'descricao' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ]
        );

        if ($validator->fails()) {

            throw new RuntimeException(
                json_encode(
                    $validator->errors()->toArray(),
                    JSON_UNESCAPED_UNICODE
                )
            );
        }
    }
}