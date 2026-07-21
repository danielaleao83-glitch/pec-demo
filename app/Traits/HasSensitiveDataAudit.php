<?php

namespace App\Traits;

use App\Models\Paciente\PacienteAuditoria as Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

trait HasSensitiveDataAudit
{
    /**
     * Registrar auditoria governamental
     */
    public function registrarAcesso(
        ?int $userId,
        string $acao,
        string $modulo,
        ?string $registroId = null,
        ?array $dadosAntes = null,
        ?array $dadosDepois = null,
        string $nivel = 'INFO'
    ): void {
        try {
            // Usuário logado
            if (! $userId && Auth::check()) {
                $userId = Auth::id();
            }

            // IP e User-Agent
            $ip = $userId ? (app()->runningInConsole() ? '127.0.0.1' : Request::ip()) : null;
            $userAgent = $userId ? (app()->runningInConsole() ? 'console' : Request::header('User-Agent')) : null;

            // Sanitização de dados
            $dadosAntes = $dadosAntes ? $this->sanitizarDados($dadosAntes) : null;
            $dadosDepois = $dadosDepois ? $this->sanitizarDados($dadosDepois) : null;

            // Criar registro de auditoria
            Auditoria::create([
                'user_id' => $userId,
                'entidade' => $modulo,
                'entidade_id' => $registroId,
                'acao' => $acao,
                'detalhes' => [
                    'antes' => $dadosAntes,
                    'depois' => $dadosDepois,
                ],
                'ip' => $ip,
                'user_agent' => $userAgent,
                'nivel' => $nivel,
            ]);

        } catch (\Throwable $e) {
            Log::error('Falha auditoria', [
                'erro' => $e->getMessage(),
                'acao' => $acao,
                'modulo' => $modulo,
            ]);
        }
    }

    /**
     * Registrar assinatura digital de forma centralizada
     */
    public function registrarAssinatura(string $modulo, ?string $registroId, ?string $assinatura): void
    {
        if ($assinatura && Auth::check()) {
            $this->registrarAcesso(
                Auth::id(),
                'assinatura_digital',
                $modulo,
                $registroId,
                null,
                ['assinatura_medico' => $assinatura]
            );
        }
    }

    /**
     * Sanitizar dados sensíveis para auditoria
     *
     * @param  array  $camposExtras  (opcional)
     */
    protected function sanitizarDados(array $dados, array $camposExtras = []): array
    {
        $campos = array_merge([
            'cpf',
            'cpf_hash',
            'cns',
            'cns_hash',
            'password',
            'email',
            'telefone',
        ], $camposExtras);

        foreach ($campos as $campo) {
            if (isset($dados[$campo])) {
                $dados[$campo] = '***';
            }
        }

        return $dados;
    }

    /**
     * Auditoria completa para eventos de criação, atualização e exclusão
     *
     * Exemplo de uso no booted() de um modelo:
     *
     * static::creating(function($model){
     *     $model->validarCamposObrigatorios();
     *     if(Auth::check()) $model->created_by = Auth::id();
     *     $model->registrarAcesso(Auth::id(), 'create', 'nome_modulo', null, null, $model->attributesToArray());
     *     $model->registrarAssinatura('nome_modulo', null, $model->assinatura_medico ?? null);
     * });
     *
     * static::updating(function($model){
     *     $model->validarCamposObrigatorios();
     *     if(Auth::check()) $model->updated_by = Auth::id();
     *     $model->registrarAcesso(Auth::id(), 'update', 'nome_modulo', $model->id, $model->getOriginal(), $model->getDirty());
     *     $model->registrarAssinatura('nome_modulo', $model->id, $model->assinatura_medico ?? null);
     * });
     *
     * static::deleting(function($model){
     *     $model->registrarAcesso(Auth::id(), 'delete', 'nome_modulo', $model->id, $model->attributesToArray(), null);
     * });
     */
}
