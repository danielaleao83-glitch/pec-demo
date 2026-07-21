<?php

namespace App\Services\Auditoria;

use Illuminate\Support\Facades\DB;

class AuditoriaForenseService
{
    /**
     * 🔐 Verificação forense completa da cadeia de auditoria
     */
    public function verificar(): array
    {
        $anteriorHash = null;
        $resultado = ['status' => 'integro'];

        DB::table('auditorias')
            ->orderBy('executado_em')
            ->chunk(500, function ($registros) use (&$anteriorHash, &$resultado) {

                foreach ($registros as $r) {

                    $payload = [
                        'user_id' => $r->user_id,
                        'acao' => $r->acao,
                        'modulo' => $r->modulo,
                        'registro_id' => $r->registro_id,
                        'dados_antes' => json_decode($r->dados_antes, true),
                        'dados_depois' => json_decode($r->dados_depois, true),
                        'executado_em' => $r->executado_em,
                    ];

                    $hashCalculado = AuditoriaChainService::gerar(
                        $payload,
                        $r->hash_anterior
                    );

                    /**
                     * 🚨 1. HASH INTEGRIDADE
                     */
                    if ($hashCalculado !== $r->hash_integridade) {
                        $resultado = [
                            'status' => 'hash_invalido',
                            'id' => $r->id
                        ];
                        return false;
                    }

                    /**
                     * 🚨 2. CADEIA
                     */
                    if (
                        $anteriorHash !== null &&
                        $r->hash_anterior !== $anteriorHash
                    ) {
                        $resultado = [
                            'status' => 'cadeia_quebrada',
                            'id' => $r->id
                        ];
                        return false;
                    }

                    /**
                     * 🔐 3. ASSINATURA DIGITAL
                     */
                    $valido = app(AuditoriaSignatureService::class)
                        ->verificar(
                            ['hash' => $r->hash_integridade],
                            $r->assinatura
                        );

                    if (!$valido) {
                        $resultado = [
                            'status' => 'assinatura_invalida',
                            'id' => $r->id
                        ];
                        return false;
                    }

                    $anteriorHash = $r->hash_integridade;
                }

                return true;
            });

        return $resultado;
    }
}