<?php

declare(strict_types=1);

namespace App\Application\Services\CNES;

use Symfony\Component\HttpFoundation\Response;

final class CNESRequestValidator
{
    public function validate(
        string $cnes
    ): array {

        abort_if(
            ! preg_match('/^[0-9]{7}$/', $cnes),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'CNES inválido.'
        );

        return [

            'cnes' => trim($cnes),
        ];
    }
}