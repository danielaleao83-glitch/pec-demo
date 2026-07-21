<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Factories;

class SisabMetadataFactory
{
    public static function make(
        array $context
    ): array {

        return [

            'trace_id' =>
                $context['trace_id'],

            'fingerprint' =>
                $context['fingerprint'],

            'environment' =>
                $context['environment'],

            'generated_at' =>
                now()->toIso8601String(),

            'request_hash' => hash(
                'sha256',
                json_encode(
                    $context,
                    JSON_UNESCAPED_UNICODE
                )
            ),
        ];
    }
}