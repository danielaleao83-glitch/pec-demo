<?php

declare(strict_types=1);

namespace App\Application\Services\SOAP;

final class SoapSecurityService
{
    public function payloadHash(
        string $payload
    ): string {

        return hash(
            'sha512',
            $payload
        );
    }
}