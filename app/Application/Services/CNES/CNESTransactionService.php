<?php

declare(strict_types=1);

namespace App\Application\Services\CNES;

use App\Application\Services\Security\SecurityRateLimitService;
use App\Infrastructure\Security\CorrelationIdResolver;
use App\Infrastructure\Security\FingerprintGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CNESTransactionService
{
    public function __construct(
        private readonly CNESConsultaService $consulta,
        private readonly CNESRequestValidator $validator,
        private readonly CNESHashService $hash,
        private readonly CNESAuditService $audit,
        private readonly CNESResponseBuilder $response,
        private readonly CNESExecutionLogger $logger,
        private readonly CorrelationIdResolver $correlation,
        private readonly FingerprintGenerator $fingerprint,
        private readonly SecurityRateLimitService $rateLimit,
    ) {}

    public function execute(
        Request $request,
        string $cnes
    ): JsonResponse {

        $startedAt = microtime(true);

        $correlationId =
            $this->correlation->resolve($request);

        try {

            $this->logger->start(
                request: $request,
                correlationId: $correlationId
            );

            $this->rateLimit->ensure(
                key: sha1($request->ip()),
                maxAttempts: 120,
                decaySeconds: 60
            );

            $validated =
                $this->validator->validate($cnes);

            $fingerprint =
                $this->fingerprint->generate(
                    request: $request,
                    payload: $validated
                );

            $response = DB::transaction(
                function () use (
                    $validated,
                    $correlationId,
                    $fingerprint,
                    $request
                ) {

                    $unidade =
                        $this->consulta->consultar(
                            cnes: $validated['cnes'],
                            correlationId: $correlationId
                        );

                    $hashIntegridade =
                        $this->hash->generate(
                            cnes: $validated['cnes'],
                            correlationId: $correlationId,
                            fingerprint: $fingerprint
                        );

                    $this->audit->log(
                        action: 'CNES_CONSULTA',
                        correlationId: $correlationId,
                        payload: [

                            'cnes'
                                => $validated['cnes'],

                            'fingerprint'
                                => $fingerprint,

                            'hash_integridade'
                                => $hashIntegridade,

                            'ip'
                                => $request->ip(),
                        ]
                    );

                    return $this->response->success(
                        cnes: $validated['cnes'],
                        unidade: $unidade,
                        correlationId: $correlationId,
                        fingerprint: $fingerprint,
                        hashIntegridade: $hashIntegridade
                    );
                }
            );

            $this->logger->success(
                correlationId: $correlationId,
                executionTime: round(
                    microtime(true) - $startedAt,
                    5
                ),
                request: $request
            );

            return $response;

        } catch (Throwable $exception) {

            $this->logger->failure(
                exception: $exception,
                correlationId: $correlationId,
                request: $request
            );

            return $this->response->error(
                correlationId: $correlationId
            );
        }
    }
}