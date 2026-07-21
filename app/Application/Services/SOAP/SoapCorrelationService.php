<?php

declare(strict_types=1);

namespace App\Application\Services\SOAP;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class SoapCorrelationService
{
    public function generate(
        Request $request
    ): string {

        return $request->header(
            'X-Correlation-ID'
        ) ?: Uuid::uuid7()->toString();
    }
}