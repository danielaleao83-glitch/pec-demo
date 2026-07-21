<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login\Actions;

use App\Application\Services\Auth\Login\Security\CorrelationIdResolver;
use App\Application\Services\Auth\Login\Security\LoginSecurityLogger;
use Illuminate\Http\Request;

final class StartLoginSecurityContextAction
{
    public function __construct(
        private readonly CorrelationIdResolver $resolver,
        private readonly LoginSecurityLogger $logger,
    ) {}

    public function execute(
        Request $request
    ): array {

        $correlationId =
            $this->resolver
                ->resolve($request);

        $this->logger->started(
            request: $request,
            correlationId:
                $correlationId,
        );

        return [

            'correlation_id'
                => $correlationId,

            'started_at'
                => microtime(true),
        ];
    }
}