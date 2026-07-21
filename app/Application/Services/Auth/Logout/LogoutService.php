<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Logout;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * =========================================================
 * 🔐 LOGOUT SERVICE
 * =========================================================
 */
final class LogoutService
{
    public function __construct(
        private readonly LogoutTokenRevoker $tokenRevoker,
        private readonly LogoutAuditService $auditService,
        private readonly LogoutSecurityLogger $logger,
        private readonly LogoutHashService $hashService,
        private readonly LogoutResponseFactory $responseFactory,
        private readonly LogoutCorrelationService $correlationService,
    ) {}

    /**
     * =========================================================
     * 🚪 EXECUTA LOGOUT
     * =========================================================
     */
    public function execute(
        Request $request
    ): array {

        $startedAt = microtime(true);

        $correlationId =
            $this->correlationService
                ->resolve($request);

        try {

            return DB::transaction(
                function () use (
                    $request,
                    $correlationId,
                    $startedAt
                ) {

                    $user =
                        $request->user();

                    /**
                     * 🔐 Revoga token
                     */
                    $this->tokenRevoker
                        ->revoke($user);

                    /**
                     * 🔐 Hash integridade
                     */
                    $hashIntegridade =
                        $this->hashService
                            ->generate(
                                userId:
                                    (string) $user?->id,

                                correlationId:
                                    $correlationId
                            );

                    /**
                     * 🧾 Auditoria
                     */
                    $this->auditService
                        ->register(
                            user:
                                $user,

                            correlationId:
                                $correlationId,

                            ip:
                                $request->ip(),

                            userAgent:
                                $request->userAgent(),

                            hashIntegridade:
                                $hashIntegridade
                        );

                    /**
                     * 📈 LOG
                     */
                    $this->logger->success(
                        [

                            'user_id'
                                => $user?->id,

                            'correlation_id'
                                => $correlationId,

                            'execution_time'
                                => round(
                                    microtime(true)
                                    - $startedAt,
                                    5
                                ),

                            'ip'
                                => $request->ip(),
                        ]
                    );

                    return $this->responseFactory
                        ->make(
                            correlationId:
                                $correlationId,

                            hashIntegridade:
                                $hashIntegridade
                        );
                }
            );

        } catch (Throwable $exception) {

            $this->logger->failure(
                exception:
                    $exception,

                context: [

                    'correlation_id'
                        => $correlationId,
                ]
            );

            throw $exception;
        }
    }
}