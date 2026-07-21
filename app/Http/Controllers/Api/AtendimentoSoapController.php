<?php

declare(strict_types=1);

namespace App\Http\Controllers\Integracao;

use App\Application\Services\SOAP\SoapTransactionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AtendimentoSoapController extends Controller
{
    public function __construct(
        private readonly SoapTransactionService $service
    ) {}

    public function handle(
        Request $request
    ): JsonResponse {

        return $this->service->handle($request);
    }
}