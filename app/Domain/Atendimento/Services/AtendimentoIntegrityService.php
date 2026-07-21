<?php

declare(strict_types=1);

namespace App\Domain\Atendimento\Services;

use App\Domain\Atendimento\Entities\Atendimento;

final class AtendimentoIntegrityService
{
    public function generate(
        Atendimento $atendimento,
        ?string $previousHash = null
    ): string {

        $payload = [

            'schema_name' => 'atendimento_integrity',

            'schema_version' => 1,

            'domain' => 'atendimento',

            'aggregate_id'
                => $atendimento->id()->value(),

            'uuid'
                => $atendimento->uuid(),

            'status'
                => $atendimento->status()->value,

            'correlation_id'
                => $atendimento->correlationId(),

            'aggregate_version'
                => $atendimento->version(),

            'event_count'
                => $atendimento->eventCount(),

            'previous_hash'
                => $previousHash,
        ];

        ksort($payload);

        return hash_hmac(
            'sha256',
            json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            ),
            config('app.audit_key')
        );
    }
}