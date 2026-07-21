<?php

declare(strict_types=1);

namespace App\Application\Services\CNES;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CNESExecutionLogger
{
    public function start(
        Request $request,
        string $correlationId
    ): void {

        Log::channel('security')->info(
            'CNES_REQUEST_STARTED',
            [

                'correlation_id'
                    => $correlationId,

                'ip'
                    => $request->ip(),

                'path'
                    => $request->path(),
            ]
        );
    }

    public function success(
        string $correlationId,
        float $executionTime,
        Request $request
    ): void {

        Log::channel('security')->info(
            'CNES_REQUEST_SUCCESS',
            [

                'correlation_id'
                    => $correlationId,

                'execution_time'
                    => $executionTime,

                'ip'
                    => $request->ip(),
            ]
        );
    }

    public function failure(
        Throwable $exception,
        string $correlationId,
        Request $request
    ): void {

        Log::channel('security')->critical(
            'CNES_REQUEST_FAILURE',
            [

                'message'
                    => $exception->getMessage(),

                'correlation_id'
                    => $correlationId,

                'ip'
                    => $request->ip(),
            ]
        );
    }
}