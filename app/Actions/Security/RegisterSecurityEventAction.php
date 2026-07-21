<?php

namespace App\Actions\Security;

use Illuminate\Support\Facades\Log;

class RegisterSecurityEventAction
{
    public function execute(string $event, array $context = []): void
    {
        $request = request();

        Log::channel('security')->info($event, [
            'user_id' => auth()->id(),
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'route' => $request?->path(),
            'method' => $request?->method(),

            'context' => $context,

            'timestamp' => now()->toISOString(),
        ]);
    }
}