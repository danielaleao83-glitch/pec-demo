<?php

declare(strict_types=1);

namespace App\Application\Services\CNES;

use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;

final class CNESResponseBuilder
{
    public function success(
        string $cnes,
        mixed $unidade,
        string $correlationId,
        string $fingerprint,
        string $hashIntegridade
    ): JsonResponse {

        return response()->json([

            'success' => true,

            'data' => [

                'uuid'
                    => Uuid::uuid7()->toString(),

                'cnes'
                    => $cnes,

                'unidade'
                    => $unidade,
            ],

            'meta' => [

                'correlation_id'
                    => $correlationId,

                'fingerprint'
                    => $fingerprint,

                'hash_integridade'
                    => $hashIntegridade,

                'timestamp'
                    => now()->toIso8601String(),
            ]
        ]);
    }

    public function error(
        string $correlationId
    ): JsonResponse {

        return response()->json([

            'success' => false,

            'message'
                => 'Falha consulta CNES.',

            'correlation_id'
                => $correlationId,

            'timestamp'
                => now()->toIso8601String(),
        ], 500);
    }
}