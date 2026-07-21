<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class LoginSecurityLogger
{
    public function success(
        User $user,
        Request $request,
        string $correlationId,
        string $sessionUuid,
        float $startedAt
    ): void {

        Log::channel('security')->info(
            'AUTH_LOGIN_SUCCESS',
            [

                'user_id'
                    => $user->id,

                'email'
                    => $user->email,

                'session_uuid'
                    => $sessionUuid,

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

                'timestamp'
                    => now()->toIso8601String(),
            ]
        );
    }

    public function failure(
        Throwable $exception,
        Request $request
    ): void {

        Log::channel('security')->critical(
            'AUTH_LOGIN_FAILURE',
            [

                'message'
                    => $exception->getMessage(),

                'ip'
                    => $request->ip(),

                'user_agent'
                    => substr(
                        (string) $request->userAgent(),
                        0,
                        500
                    ),

                'timestamp'
                    => now()->toIso8601String(),
            ]
        );
    }
}