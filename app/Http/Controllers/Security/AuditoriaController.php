<?php

declare(strict_types=1);

namespace App\Http\Controllers\Security;

use App\Application\Services\Auditoria\RegistrarAuditoriaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * =========================================================
 * 🔐 AUDITORIA CONTROLLER
 * =========================================================
 *
 * ✔ Auditoria federal
 * ✔ Segurança RNDS
 * ✔ Blindagem LGPD
 *
 * =========================================================
 */
final class AuditoriaController
    extends Controller
{
    public function __construct(
        private readonly RegistrarAuditoriaService $service,
    ) {}

    public function store(
        Request $request
    ): JsonResponse {

        try {

            $log =
                $this->service->execute(

                    acao:
                        (string) $request->input(
                            'acao'
                        ),

                    modulo:
                        (string) $request->input(
                            'modulo'
                        ),

                    payload:
                        $request->all(),

                    userId:
                        optional(
                            $request->user()
                        )->id,

                    ip:
                        $request->ip(),

                    userAgent:
                        $request->userAgent(),

                    correlationId:
                        $request->header(
                            'X-Correlation-ID'
                        ),
                );

            return response()->json(
                [

                    'success' => true,

                    'data'
                        => $log->toArray(),
                ],
                Response::HTTP_CREATED
            );

        } catch (Throwable $exception) {

            return response()->json(
                [

                    'success' => false,

                    'message'
                        => $exception
                            ->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}