<?php

declare(strict_types=1);

namespace App\Http\Controllers\Atendimento\Api;

use App\Application\Services\Atendimento\RegistrarAtendimentoService;
use App\Application\Services\Auditoria\RegistrarAuditoriaService;
use App\Application\Services\Security\CorrelationIdService;
use App\Application\Services\Security\RequestFingerprintService;
use App\Application\Services\Security\SecurityRateLimitService;
use App\Domain\Atendimento\DTO\RegistrarAtendimentoDTO;
use App\Http\Controllers\Controller;
use App\Support\Security\SecurityContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * =========================================================
 * 🏥 RNDS / e-SUS APS / DATASUS
 * 🔐 ATENDIMENTO STORE CONTROLLER
 * =========================================================
 *
 * ✔ Produção federal
 * ✔ UUID v7
 * ✔ Blindagem hospitalar
 * ✔ Auditoria forense
 * ✔ LGPD
 * ✔ Anti flood
 * ✔ Correlation ID
 * ✔ Integridade SHA512
 * ✔ Observabilidade distribuída
 * ✔ Segurança nacional
 *
 * =========================================================
 */
final class AtendimentoStoreController
    extends Controller
{
    /**
     * 🚫 Limite
     */
    private const MAX_REQUESTS = 120;

    public function __construct(
        private readonly RegistrarAtendimentoService $service,
        private readonly CorrelationIdService $correlation,
        private readonly RequestFingerprintService $fingerprint,
        private readonly SecurityRateLimitService $rateLimit,
        private readonly RegistrarAuditoriaService $audit,
        private readonly SecurityContext $securityContext,
    ) {}

    /**
     * =========================================================
     * 🏥 REGISTRAR ATENDIMENTO
     * =========================================================
     */
    public function store(
        Request $request
    ): JsonResponse {

        $startedAt = microtime(true);

        $correlationId =
            $this->correlation->resolve(
                $request
            );

        try {

            /**
             * =================================================
             * 🔐 CONTEXTO
             * =================================================
             */
            $this->securityContext->boot(
                request: $request,
                correlationId: $correlationId,
                modulo: 'ATENDIMENTO_STORE'
            );

            /**
             * =================================================
             * 🚫 RATE LIMIT
             * =================================================
             */
            $this->rateLimit->ensure(
                key: sha1($request->ip()),
                maxAttempts: self::MAX_REQUESTS,
                decaySeconds: 60
            );

            /**
             * =================================================
             * 🔒 VALIDAÇÃO
             * =================================================
             */
            $validated =
                $this->validateRequest(
                    $request
                );

            /**
             * =================================================
             * 🔐 FINGERPRINT
             * =================================================
             */
            $fingerprint =
                $this->fingerprint->generate(
                    request: $request,
                    payload: $validated
                );

            /**
             * =================================================
             * 🧠 DTO
             * =================================================
             */
            $dto =
                new RegistrarAtendimentoDTO(

                    pacienteId:
                        $validated['paciente_id'],

                    prioridade:
                        (int) $validated['prioridade'],

                    unidadeId:
                        $validated['unidade_id'],

                    profissionalId:
                        $validated['profissional_id'],

                    observacao:
                        $validated['observacao']
                            ?? null,
                );

            /**
             * =================================================
             * 🏥 TRANSAÇÃO
             * =================================================
             */
            $atendimento =
                DB::transaction(
                    fn () =>
                        $this->service->execute(
                            dto: $dto,
                            correlationId:
                                $correlationId
                        )
                );

            /**
             * =================================================
             * 🔐 HASH
             * =================================================
             */
            $hashIntegridade =
                hash(
                    'sha512',
                    implode('|', [

                        $atendimento->uuid(),

                        $fingerprint,

                        $correlationId,

                        now()->timestamp,

                        config('app.key'),
                    ])
                );

            /**
             * =================================================
             * 🧾 AUDITORIA
             * =================================================
             */
            $this->audit->execute(

                acao:
                    'ATENDIMENTO_CRIADO',

                modulo:
                    'ATENDIMENTO',

                payload: [

                    'uuid'
                        => $atendimento->uuid(),

                    'paciente_id'
                        => $validated['paciente_id'],

                    'fingerprint'
                        => $fingerprint,

                    'hash_integridade'
                        => $hashIntegridade,
                ],

                userId:
                    optional(
                        $request->user()
                    )->id,

                ip:
                    $request->ip(),

                userAgent:
                    $request->userAgent(),

                correlationId:
                    $correlationId,
            );

            /**
             * =================================================
             * 📈 PERFORMANCE
             * =================================================
             */
            $executionTime = round(
                microtime(true) - $startedAt,
                5
            );

            Log::channel('security')->info(
                'ATENDIMENTO_CREATED',
                [

                    'uuid'
                        => $atendimento->uuid(),

                    'correlation_id'
                        => $correlationId,

                    'execution_time'
                        => $executionTime,
                ]
            );

            return response()->json(
                [

                    'success'
                        => true,

                    'uuid'
                        => Uuid::uuid7()
                            ->toString(),

                    'data' => [

                        'atendimento'
                            => $atendimento
                                ->toArray(),
                    ],

                    'meta' => [

                        'correlation_id'
                            => $correlationId,

                        'fingerprint'
                            => $fingerprint,

                        'hash_integridade'
                            => $hashIntegridade,

                        'timestamp'
                            => now()
                                ->toIso8601String(),

                        'execution_time'
                            => $executionTime,
                    ]
                ],
                Response::HTTP_CREATED
            );

        } catch (Throwable $exception) {

            Log::channel('security')->critical(
                'ATENDIMENTO_STORE_FAILURE',
                [

                    'message'
                        => $exception
                            ->getMessage(),

                    'trace'
                        => substr(
                            $exception
                                ->getTraceAsString(),
                            0,
                            5000
                        ),

                    'correlation_id'
                        => $correlationId,

                    'ip'
                        => $request->ip(),
                ]
            );

            return response()->json(
                [

                    'success'
                        => false,

                    'message'
                        => 'Falha ao registrar atendimento.',

                    'correlation_id'
                        => $correlationId,

                    'timestamp'
                        => now()
                            ->toIso8601String(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * =========================================================
     * 🔒 VALIDAÇÃO
     * =========================================================
     */
    private function validateRequest(
        Request $request
    ): array {

        return $request->validate([

            'paciente_id' => [

                'required',
                'uuid',
            ],

            'prioridade' => [

                'required',
                'integer',
                'between:1,5',
            ],

            'unidade_id' => [

                'required',
                'uuid',
            ],

            'profissional_id' => [

                'required',
                'uuid',
            ],

            'observacao' => [

                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }
}