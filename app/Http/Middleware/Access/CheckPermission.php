<?php

namespace App\Http\Middleware\Access;

use App\Services\AuditoriaService;
use App\Services\SecurityMonitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    /**
     * 🔐 Middleware de permissão (nível SUS federal)
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        try {

            $user = Auth::user();

            $context = $this->context($request, $permissions, $user);

            /*
            |------------------------------------------------------
            | 🚨 NÃO AUTENTICADO
            |------------------------------------------------------
            */
            if (! $user) {

                $this->auditar('acesso_nao_autenticado', 'auth', null, $context);

                abort(401, 'Não autenticado');
            }

            /*
            |------------------------------------------------------
            | ⚠️ CONFIGURAÇÃO INVÁLIDA
            |------------------------------------------------------
            */
            if (empty($permissions)) {

                Log::critical('Middleware CheckPermission sem parâmetros', $context);

                $this->auditar('erro_configuracao', 'middleware', null, $context);

                abort(500, 'Erro de configuração de permissão');
            }

            /*
            |------------------------------------------------------
            | 🏛️ ADMIN BYPASS (nível federal)
            |------------------------------------------------------
            */
            if ($user->isAdmin()) {

                $this->auditar('acesso_admin', 'permissao', null, $context);

                return $next($request);
            }

            /*
            |------------------------------------------------------
            | 🔐 VERIFICAÇÃO DE PERMISSÕES
            |------------------------------------------------------
            */
            $temPermissao = $this->verificarPermissao($user, $permissions);

            if (! $temPermissao) {

                $this->auditar('acesso_negado', 'permissao', null, $context);

                try {
                    SecurityMonitor::verificarAcessoSuspeito($user, $request);
                } catch (\Throwable $e) {
                    Log::warning('SecurityMonitor falhou', [
                        'erro' => $e->getMessage(),
                        'user_id' => $user->id,
                    ]);
                }

                abort(403, 'Sem permissão para acessar este recurso');
            }

            /*
            |------------------------------------------------------
            | ✅ ACESSO AUTORIZADO
            |------------------------------------------------------
            */
            $this->auditar('acesso_autorizado', 'permissao', null, $context);

            return $next($request);

        } catch (\Throwable $e) {

            Log::critical('ERRO CRÍTICO CHECK PERMISSION', [
                'erro' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(500, 'Erro interno de segurança de permissões');
        }
    }

    /**
     * 🔎 Verificação segura de permissões
     */
    private function verificarPermissao($user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 📊 Contexto padronizado SUS federal
     */
    private function context(Request $request, array $permissions, $user): array
    {
        return [
            'usuario_id' => $user?->id,
            'email' => $user?->email,
            'roles_usuario' => $user?->roles?->pluck('name')->toArray() ?? [],
            'role_legacy' => $user?->role?->name ?? null,
            'permissoes_requeridas' => $permissions,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'rota' => $request->route()?->getName(),
            'metodo' => $request->method(),
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * 🧠 Auditoria centralizada SUS federal
     */
    private function auditar($acao, $tipo, $registroId, $context)
    {
        try {
            AuditoriaService::registrar(
                $acao,
                $tipo,
                $registroId,
                Auth::id(),
                $context
            );
        } catch (\Throwable $e) {

            Log::error('Falha na auditoria do middleware CheckPermission', [
                'erro' => $e->getMessage(),
                'acao' => $acao,
                'user_id' => Auth::id(),
            ]);
        }
    }
}