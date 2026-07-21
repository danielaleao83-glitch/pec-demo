<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Factories;

use Illuminate\Support\Str;

class SisabPayloadFactory
{
    public static function make(
        array $dados
    ): array {

        return [

            'xml_uuid' =>
                (string) Str::uuid(),

            'paciente_uuid' =>
                $dados['paciente_uuid'] ?? null,

            'profissional_uuid' =>
                $dados['profissional_uuid'] ?? null,

            'unidade_uuid' =>
                $dados['unidade_uuid'] ?? null,

            'descricao' =>
                $dados['descricao'] ?? '',

            'data_atendimento' =>
                now()->toIso8601String(),
        ];
    }
}