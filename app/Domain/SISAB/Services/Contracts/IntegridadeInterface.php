<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Contracts;

interface IntegridadeInterface
{
    public static function gerarChainHash(
        string $hash,
        string $traceId
    ): array;
}