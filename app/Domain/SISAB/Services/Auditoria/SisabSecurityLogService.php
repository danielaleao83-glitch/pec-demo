<?php

namespace App\Services\ESusService\SISAB\Auditoria;

use Illuminate\Support\Facades\Log;

class SisabSecurityLogService
{
    public static function critical(
        string $event,
        array $context
    ): void {

        Log::channel('security')->critical(
            $event,
            $context
        );
    }
}