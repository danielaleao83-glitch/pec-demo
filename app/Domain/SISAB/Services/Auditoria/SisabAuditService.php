<?php

namespace App\Services\ESusService\SISAB\Auditoria;

use Illuminate\Support\Facades\Log;

class SisabAuditService
{
    public static function log(
        string $event,
        array $context
    ): void {

        Log::channel('audit')->info(
            $event,
            $context
        );
    }
}