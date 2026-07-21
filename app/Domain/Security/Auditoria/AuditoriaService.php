<?php

namespace App\Services\Security;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditoriaService
{
    public function registrar(
        string $acao,
        string $modulo,
        ?string $userId,
        Request $request,
        array $dados = [],
        ?string $correlationId = null
    ): void {

        $correlationId ??= app()->bound('correlation_id')
            ? app('correlation_id')
            : Str::uuid()->toString();

        Auditoria::query()->create([

            'user_id' => $userId,

            'acao' => $acao,

            'modulo' => $modulo,

            'registro_id' => $userId,

            'dados_antes' => null,

            'dados_depois' => array_merge($dados, [
                'correlation_id' => $correlationId,
            ]),

            'ip' => hash('sha256', (string) $request->ip()),

            'user_agent' => substr((string) $request->userAgent(), 0, 500),

            'url' => $request->fullUrl(),

            'metodo_http' => $request->method(),

            'executado_em' => now(),

            'hash_integridade' => hash(
                'sha256',
                implode('|', [
                    $userId ?? 'guest',
                    $acao,
                    now()->timestamp,
                    config('app.key'),
                    $correlationId,
                ])
            ),
        ]);
    }
}