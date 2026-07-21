<?php

declare(strict_types=1);

namespace App\Services\SISAB\Queue;

use App\Services\SISAB\Fingerprint\SisabFingerprintService;
use App\Services\SISAB\Idempotency\SisabIdempotencyStore;
use App\Services\SISAB\Backpressure\SisabBackpressureEngine;
use App\Services\SISAB\Jobs\SisabProcessJob;

class SisabQueueService
{
    public function enqueue(array $payload, string $missionClass)
    {
        // 1. fingerprint (identidade do evento clínico)
        $fingerprint = app(SisabFingerprintService::class)
            ->generate($payload);

        // 2. idempotência persistente (evita duplicação real)
        $idempotency = app(SisabIdempotencyStore::class);

        if ($idempotency->exists($fingerprint)) {
            return [
                'status' => 'duplicate',
                'fingerprint' => $fingerprint
            ];
        }

        // 3. backpressure (controle de carga federal)
        $pressureEngine = app(SisabBackpressureEngine::class);
        $pressure = $pressureEngine->evaluate();

        if (! $this->canAccept($pressure, $missionClass)) {
            return [
                'status' => 'rejected_backpressure',
                'pressure' => $pressure
            ];
        }

        // 4. reserva idempotente atômica
        $idempotency->reserve($fingerprint);

        // 5. criação do job clínico
        $job = new SisabProcessJob(
            payload: $payload,
            fingerprint: $fingerprint,
            missionClass: $missionClass
        );

        // 6. envio para fila com prioridade dinâmica
        dispatch($job)
            ->onQueue($this->resolveQueue($missionClass, $pressure))
            ->afterCommit();

        return [
            'status' => 'queued',
            'fingerprint' => $fingerprint,
            'pressure' => $pressure,
            'queue' => $this->resolveQueue($missionClass, $pressure),
        ];
    }

    private function resolveQueue(string $class, string $pressure): string
    {
        return match (true) {

            $class === 'critical'
                => 'queue_critical',

            in_array($pressure, ['RED', 'CRITICAL'])
                => 'queue_priority',

            $class === 'clinical'
                => 'queue_clinical',

            $class === 'batch'
                => 'queue_batch',

            default
                => 'queue_low',
        };
    }

    private function canAccept(string $pressure, string $missionClass): bool
    {
        return match ($pressure) {

            'YELLOW' => true,

            'ORANGE' => in_array($missionClass, [
                'critical',
                'clinical'
            ]),

            'RED' => $missionClass === 'critical',

            'CRITICAL' => $missionClass === 'critical',

            default => false,
        };
    }
}