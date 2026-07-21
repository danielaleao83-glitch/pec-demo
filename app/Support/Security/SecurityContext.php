<?php

declare(strict_types=1);

namespace App\Support\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class SecurityContext
{
    public function boot(
        Request $request,
        string $correlationId,
        string $modulo
    ): void {

        Log::channel('security')->info(
            'SECURITY_CONTEXT_BOOT',
            [

                'modulo'
                    => $modulo,

                'correlation_id'
                    => $correlationId,

                'ip'
                    => $request->ip(),

                'method'
                    => $request->method(),

                'path'
                    => $request->path(),

                'user_agent'
                    => substr(
                        (string) $request->userAgent(),
                        0,
                        500
                    ),

                'timestamp'
                    => now()
                        ->toIso8601String(),
            ]
        );
    }
}