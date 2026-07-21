<?php

declare(strict_types=1);

namespace App\Http\Controllers\Producao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Producao\GerarBpaRequest;
use App\Services\Producao\BpaProducaoService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProducaoController extends Controller
{
    public function __construct(
        private readonly BpaProducaoService $service
    ) {
        $this->middleware([
            'auth:sanctum',
            'throttle:clinico',
            'security.headers',
        ]);
    }

    /**
     * 📄 GERAR BPA DATASUS
     */
    public function gerarBPAOficial(
        GerarBpaRequest $request
    ): JsonResponse {

        $response = $this->service->gerarBpa(
            $request->validated()
        );

        return response()->json(
            $response,
            $response['status'] === 'success'
                ? Response::HTTP_OK
                : Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * 📥 DOWNLOAD BPA
     */
    public function download(
        string $arquivo
    ) {
        return $this->service->download($arquivo);
    }
}