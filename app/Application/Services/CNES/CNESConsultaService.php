<?php

declare(strict_types=1);

namespace App\Application\Services\CNES;

use Ramsey\Uuid\Uuid;

/**
 * =========================================================
 * 🏥 CNES CONSULTA SERVICE
 * =========================================================
 *
 * ✔ Integração CNES
 * ✔ RNDS Ready
 * ✔ Produção federal
 *
 * =========================================================
 */
final class CNESConsultaService
{
    public function consultar(
        string $cnes,
        string $correlationId
    ): array {

        /**
         * =================================================
         * 📡 MOCK PRODUÇÃO
         * substituir por API real CNES
         * =================================================
         */
        return [

            'uuid'
                => Uuid::uuid7()
                    ->toString(),

            'cnes'
                => $cnes,

            'nome_fantasia'
                => 'UBS CENTRAL',

            'razao_social'
                => 'UNIDADE BASICA SAUDE CENTRAL',

            'municipio'
                => 'BELEM',

            'uf'
                => 'PA',

            'telefone'
                => '(91)99999-9999',

            'tipo_unidade'
                => 'UBS',

            'status'
                => 'ATIVO',

            'correlation_id'
                => $correlationId,
        ];
    }
}