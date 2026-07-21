<?php

declare(strict_types=1);

namespace App\Application\Services\SOAP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SoapTransactionService
{
    public function __construct(
        private readonly SoapRequestValidator $validator,
        private readonly SoapSecurityService $security,
        private readonly SoapReplayProtectionService $replay,
        private readonly SoapRateLimitService $rateLimit,
        private readonly SoapAuditService $audit,
        private readonly SoapCorrelationService $correlation,
        private readonly SoapResponseBuilder $response,
    ) {}

    public function handle(
        Request $request
    ): JsonResponse {

        $correlationId =
            $this->correlation->generate($request);

        $this->rateLimit->handle($request);

        $this->validator->validate($request);

        $payloadHash =
            $this->security->payloadHash(
                $request->getContent()
            );

        $this->replay->ensureNotReplay(
            $payloadHash
        );

        return DB::transaction(
            function () use (
                $request,
                $correlationId,
                $payloadHash
            ) {

                $this->audit->log(
                    action: 'SOAP_RECEBIDO',
                    correlationId: $correlationId,
                    payloadHash: $payloadHash,
                );

                return $this->response->success([
                    'correlation_id' => $correlationId,
                    'payload_hash' => $payloadHash,
                ]);
            }
        );
    }
}