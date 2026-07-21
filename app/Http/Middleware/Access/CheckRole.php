<?php

namespace App\Http\Middleware\Access;

use App\Services\AuditoriaService;
use App\Services\SecurityMonitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * 🏥 Middleware de controle de acesso por role (SUS federal)
     */
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        try {

            $user = Auth::user();

            $context = $this->context($request, $roles, $user);

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
            if (empty($roles)) {

                Log::critical('CheckRole sem parâmetros configurados', $context);

                $this->auditar('erro_configuracao', 'middleware', null, $context);

                abort(500, 'Erro de configuração de acesso');
            }

            /*
            |------------------------------------------------------
            | 🏛️ ADMIN BYPASS (auditado)
            |------------------------------------------------------
            */
            if ($user->isAdmin()) {

                $this->auditar('acesso_admin', 'role', null, $context);

                return $next($request);
            }

            /*
            |------------------------------------------------------
            | 🔐 VERIFICAÇÃO DE ROLES
            |------------------------------------------------------
            */
            if (! $this->hasRole($user, $roles)) {

                $this->auditar('acesso_negado', 'role', null, $context);

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
            $this->auditar('acesso_autorizado', 'role', null, $context);

            return $next($request);

        } catch (\Throwable $e) {

            Log::critical('ERRO CRÍTICO CHECK ROLE', [
                'erro' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(500, 'Erro interno de controle de acesso');
        }
    }

    /**
     * 🔎 Verificação otimizada de roles
     */
    private function hasRole($user, array $roles): bool
    {
        return $user->hasAnyRole($roles);
    }

    /**
     * 📊 Contexto padronizado SUS federal
     */
    private function context(Request $request, array $roles, $user): array
    {
        return [
            'usuario_id' => $user?->id,
            'email' => $user?->email,
            'roles_usuario' => $user?->roles?->pluck('name')->toArray() ?? [],
            'role_legacy' => $user?->role?->name ?? null,
            'roles_requeridas' => $roles,
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

            Log::error('Falha auditoria CheckRole', [
                'erro' => $e->getMessage(),
                'acao' => $acao,
                'user_id' => Auth::id(),
            ]);
        }
    }
}