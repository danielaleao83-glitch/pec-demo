<?php

declare(strict_types=1);

namespace App\Application\Services\SOAP;

use Illuminate\Http\JsonResponse;

final class SoapResponseBuilder
{
    public function success(
        array $data = []
    ): JsonResponse {

        return response()->json([
            'success' => true,
            'data' => $data,
            'timestamp'
                => now()->toIso8601String(),
        ]);
    }

    public function error(
        string $message
    ): JsonResponse {

        return response()->json([
            'success' => false,
            'message' => $message,
            'timestamp'
                => now()->toIso8601String(),
        ], 500);
    }
}