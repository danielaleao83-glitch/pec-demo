<?php

declare(strict_types=1);

namespace App\Application\Handlers\RNDS;

use App\Domain\Paciente\Events\PacienteSincronizadoRNDS;
use App\Infrastructure\Integrations\RNDS\RNDSClient;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * =========================================================
 * 🏥 SYNC PACIENTE HANDLER
 * 🔐 RNDS / DATASUS / e-SUS APS
 * =========================================================
 *
 * ✔ Produção federal
 * ✔ LGPD
 * ✔ Segurança hospitalar
 * ✔ Rastreabilidade nacional
 * ✔ Blindagem distribuída
 * ✔ Auditoria forense
 *
 * =========================================================
 */
final class SyncPacienteHandler
{
    public function __construct(
        private readonly RNDSClient $rndsClient,
    ) {}

    /**
     * =========================================================
     * 📡 HANDLE
     * =========================================================
     */
    public function handle(
        PacienteSincronizadoRNDS $event
    ): void {

        $startedAt = microtime(true);

        try {

            /**
             * =================================================
             * 📡 PAYLOAD RNDS
             * =================================================
             */
            $payload = [

                'paciente_id'
                    => $event->pacienteId,

                'cns'
                    => $event->cns,

                'cpf'
                    => $event->cpf,

                'nome'
                    => $event->nome,

                'sexo'
                    => $event->sexo,

                'data_nascimento'
                    => $event->dataNascimento,

                'correlation_id'
                    => $event->correlationId,

                'sincronizado_em'
                    => $event->timestamp
                        ->format(DATE_ATOM),
            ];

            /**
             * =================================================
             * 📡 RNDS
             * =================================================
             */
            $response =
                $this->rndsClient
                    ->syncPaciente(
                        $payload
                    );

            /**
             * =================================================
             * 🔐 HASH FORENSE
             * =================================================
             */
            $hashIntegridade =
                hash(
                    'sha512',
                    json_encode(
                        $payload,
                        JSON_UNESCAPED_UNICODE
                    )
                );

            /**
             * =================================================
             * 📈 LOG
             * =================================================
             */
            Log::channel('security')->info(
                'RNDS_SYNC_PACIENTE_SUCCESS',
                [

                    'paciente_id'
                        => $event->pacienteId,

                    'correlation_id'
                        => $event->correlationId,

                    'hash_integridade'
                        => $hashIntegridade,

                    'execution_time'
                        => round(
                            microtime(true)
                            - $startedAt,
                            5
                        ),

                    'response'
                        => $response,
                ]
            );

        } catch (Throwable $exception) {

            /**
             * =================================================
             * 🚨 LOG FORENSE
             * =================================================
             */
            Log::channel('security')->critical(
                'RNDS_SYNC_PACIENTE_FAILURE',
                [

                    'message'
                        => $exception
                            ->getMessage(),

                    'trace'
                        => substr(
                            $exception
                                ->getTraceAsString(),
                            0,
                            5000
                        ),

                    'paciente_id'
                        => $event->pacienteId,

                    'correlation_id'
                        => $event->correlationId,

                    'timestamp'
                        => now()
                            ->toIso8601String(),
                ]
            );

            throw $exception;
        }
    }
}