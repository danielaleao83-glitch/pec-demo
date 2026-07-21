<?php

declare(strict_types=1);

namespace App\Http\Controllers\Integracao;

use App\Application\Services\CNES\CNESTransactionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CnesController extends Controller
{
    public function __construct(
        private readonly CNESTransactionService $service
    ) {}

    public function show(
        Request $request,
        string $cnes
    ): JsonResponse {

        return $this->service->execute(
            request: $request,
            cnes: $cnes
        );
    }
}