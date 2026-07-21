<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Transformacao;

use App\Services\ESusService\SISAB\Auditoria\SisabAuditService;
use App\Services\ESusService\SISAB\Auditoria\SisabSecurityLogService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SisabTransformadorXmlService
{
    /**
     * =========================================================
     * 🔐 CAMPOS PERMITIDOS
     * =========================================================
     */
    protected static array $allowedFields = [

        'paciente_uuid',

        'profissional_uuid',

        'unidade_uuid',

        'descricao',

        'cbo',

        'ine',

        'cpf_profissional',

        'codigo_cid',

        'codigo_ciaps',

        'tipo_atendimento',

        'sexo',

        'cpf_paciente',

        'cns_paciente',
    ];

    /**
     * =========================================================
     * 🚀 TRANSFORMA PAYLOAD
     * =========================================================
     */
    public static function transformar(
        array $dados
    ): array {

        $traceId = (string) Str::uuid();

        try {

            /**
             * =================================================
             * 🔐 FILTRA CAMPOS
             * =================================================
             */
            $dados = Arr::only(
                $dados,
                self::$allowedFields
            );

            /**
             * =================================================
             * 🧠 NORMALIZAÇÃO
             * =================================================
             */
            $dados = self::normalizar(
                $dados
            );

            /**
             * =================================================
             * 🔐 UUIDS
             * =================================================
             */
            self::validarUuids(
                $dados
            );

            /**
             * =================================================
             * 🔐 HASH PAYLOAD
             * =================================================
             */
            $payloadHash = hash(
                'sha256',
                json_encode(
                    $dados,
                    JSON_UNESCAPED_UNICODE
                )
            );

            /**
             * =================================================
             * 🧾 AUDITORIA
             * =================================================
             */
            SisabAuditService::log(
                'SISAB_TRANSFORMATION_SUCCESS',
                [

                    'trace_id' =>
                        $traceId,

                    'payload_hash' =>
                        $payloadHash,

                    'fields_count' =>
                        count($dados),

                    'timestamp' =>
                        now()->toIso8601String(),
                ]
            );

            /**
             * =================================================
             * 🚀 RETORNO
             * =================================================
             */
            return [

                'trace_id' =>
                    $traceId,

                'payload_hash' =>
                    $payloadHash,

                'dados' =>
                    $dados,
            ];

        } catch (Throwable $e) {

            /**
             * =================================================
             * 🚨 SECURITY LOG
             * =================================================
             */
            SisabSecurityLogService::critical(
                'SISAB_TRANSFORMATION_FAILURE',
                [

                    'trace_id' =>
                        $traceId,

                    'error' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),

                    'timestamp' =>
                        now()->toIso8601String(),
                ]
            );

            throw $e;
        }
    }

    /**
     * =========================================================
     * 🧠 NORMALIZA DADOS
     * =========================================================
     */
    protected static function normalizar(
        array $dados
    ): array {

        foreach ($dados as $key => $value) {

            if (!is_string($value)) {

                continue;
            }

            /**
             * =============================================
             * 🔐 REMOVE CONTROLES
             * =============================================
             */
            $value = preg_replace(
                '/[^\PC\s]/u',
                '',
                $value
            );

            /**
             * =============================================
             * 🔐 UTF8 SAFE
             * =============================================
             */
            $value = mb_convert_encoding(
                trim($value),
                'UTF-8',
                'UTF-8'
            );

            /**
             * =============================================
             * 🔐 LIMITA TAMANHO
             * =============================================
             */
            $value = mb_substr(
                $value,
                0,
                5000
            );

            $dados[$key] = $value;
        }

        return $dados;
    }

    /**
     * =========================================================
     * 🔐 VALIDA UUIDS
     * =========================================================
     */
    protected static function validarUuids(
        array $dados
    ): void {

        $uuidFields = [

            'paciente_uuid',

            'profissional_uuid',

            'unidade_uuid',
        ];

        foreach ($uuidFields as $field) {

            if (
                empty($dados[$field])
            ) {

                continue;
            }

            if (
                !Str::isUuid(
                    (string) $dados[$field]
                )
            ) {

                throw new RuntimeException(
                    "UUID inválido: {$field}"
                );
            }
        }
    }
}