<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Contracts;

interface TransformadorInterface
{
    public static function transformar(
        array $dados,
        string $xmlUuid
    ): array;
}