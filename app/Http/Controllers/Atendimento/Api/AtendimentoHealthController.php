<?php

declare(strict_types=1);

namespace App\Http\Controllers\Atendimento\Api;

use App\Application\Services\Auditoria\RegistrarAuditoriaService;
use App\Application\Services\Security\CorrelationIdService;
use App\Application\Services\Security\RequestFingerprintService;
use App\Application\Services\Security\SecurityRateLimitService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Atendimento\Api\Health\DatabaseHealthController;
use App\Http\Controllers\Atendimento\Api\Health\CacheHealthController;
use App\Http\Controllers\Atendimento\Api\Health\QueueHealthController;
use App\Http\Controllers\Atendimento\Api\Health\StorageHealthController;
use App\Http\Controllers\Atendimento\Api\Health\RedisHealthController;
use App\Support\Security\SecurityContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * =========================================================
 * 🏥 RNDS / e-SUS APS / DATASUS
 * 🔐 HEALTHCHECK FEDERAL
 * =========================================================
 *
 * ✔ Controller enxuto
 * ✔ Separação por responsabilidade
 * ✔ Observabilidade distribuída
 * ✔ UUID v7
 * ✔ Auditoria forense
 * ✔ Blindagem hospitalar
 * ✔ Healthcheck modular
 * ✔ Segurança federal
 *
 * =========================================================
 */
final class AtendimentoHealthController extends Controller
{
    private const MAX_REQUESTS = 120;

    public function __construct(
        private readonly CorrelationIdService $correlation,
        private readonly RequestFingerprintService $fingerprint,
        private readonly SecurityRateLimitService $rateLimit,
        private readonly RegistrarAuditoriaService $audit,
        private readonly SecurityContext $securityContext,

        private readonly DatabaseHealthController $database,
        private readonly CacheHealthController $cache,
        private readonly QueueHealthController $queue,
        private readonly StorageHealthController $storage,
        private readonly RedisHealthController $redis,
    ) {}

    /**
     * =========================================================
     * ❤️ HEALTHCHECK
     * =========================================================
     */
    public function index(
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
                modulo: 'ATENDIMENTO_HEALTH'
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
             * 🔐 FINGERPRINT
             * =================================================
             */
            $fingerprint =
                $this->fingerprint->generate(
                    request: $request
                );

            /**
             * =================================================
             * 🏥 HEALTHCHECKS
             * =================================================
             */
            $health = [

                'database'
                    => $this->database->check(),

                'cache'
                    => $this->cache->check(),

                'queue'
                    => $this->queue->check(),

                'storage'
                    => $this->storage->check(),

                'redis'
                    => $this->redis->check(),
            ];

            /**
             * =================================================
             * 📊 STATUS
             * =================================================
             */
            $overallStatus =
                collect($health)
                    ->contains(
                        fn ($item)
                            => $item['status']
                            !== 'UP'
                    )
                    ? 'DEGRADED'
                    : 'UP';

            /**
             * =================================================
             * 🔐 HASH
             * =================================================
             */
            $hashIntegridade =
                hash(
                    'sha512',
                    json_encode(
                        $health,
                        JSON_UNESCAPED_UNICODE
                    )
                );

            /**
             * =================================================
             * 🧾 AUDITORIA
             * =================================================
             */
            $this->audit->execute(

                acao:
                    'HEALTHCHECK_EXECUTADO',

                modulo:
                    'ATENDIMENTO_HEALTH',

                payload: [

                    'status'
                        => $overallStatus,

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
                'HEALTHCHECK_SUCCESS',
                [

                    'correlation_id'
                        => $correlationId,

                    'execution_time'
                        => $executionTime,

                    'status'
                        => $overallStatus,
                ]
            );

            return response()->json(
                [

                    'success'
                        => true,

                    'uuid'
                        => Uuid::uuid7()
                            ->toString(),

                    'service'
                        => 'eSUS_APS_Laravel',

                    'module'
                        => 'ATENDIMENTO',

                    'environment'
                        => config('app.env'),

                    'status'
                        => $overallStatus,

                    'health'
                        => $health,

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
                Response::HTTP_OK
            );

        } catch (Throwable $exception) {

            Log::channel('security')->critical(
                'HEALTHCHECK_FAILURE',
                [

                    'message'
                        => $exception->getMessage(),

                    'trace'
                        => substr(
                            $exception
                                ->getTraceAsString(),
                            0,
                            5000
                        ),

                    'correlation_id'
                        => $correlationId,
                ]
            );

            return response()->json(
                [

                    'success'
                        => false,

                    'status'
                        => 'DOWN',

                    'message'
                        => 'Falha healthcheck.',

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
}