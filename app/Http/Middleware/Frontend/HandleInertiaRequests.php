<?php

namespace App\Http\Middleware\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * ----------------------------------------------------------------------
     * 🖥️ Template raiz
     * ----------------------------------------------------------------------
     */
    protected $rootView = 'app';

    /**
     * ----------------------------------------------------------------------
     * 📦 Versionamento dos assets
     * ----------------------------------------------------------------------
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * ----------------------------------------------------------------------
     * 🌎 Shared props globais
     * ----------------------------------------------------------------------
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        /**
         * ------------------------------------------------------------------
         * 🔗 Correlation ID
         * ------------------------------------------------------------------
         */
        $correlationId = app()->bound('correlation_id')
            ? app('correlation_id')
            : (string) Str::uuid();

        /**
         * ------------------------------------------------------------------
         * 🌍 Dados da aplicação
         * ------------------------------------------------------------------
         */
        $app = [
            'name' => config('app.name'),
            'env' => app()->environment(),
            'locale' => app()->getLocale(),
            'timezone' => config('app.timezone'),
            'debug' => (bool) config('app.debug'),
        ];

        /**
         * ------------------------------------------------------------------
         * 🔐 Usuário autenticado
         * ------------------------------------------------------------------
         */
        $auth = [
            'user' => $user ? [
                'id' => $user->id,
                'uuid' => $user->uuid ?? null,

                'name' => $user->name,
                'email' => $user->email,

                /**
                 * 🔐 Roles seguras
                 */
                'roles' => method_exists($user, 'roles')
                    ? $user->roles->pluck('name')->values()
                    : [],

                /**
                 * 🔐 Permissões
                 */
                'permissions' => method_exists($user, 'permissions')
                    ? $user->permissions->pluck('name')->values()
                    : [],

                /**
                 * 🔐 Role legado
                 */
                'role_legacy' => $user->role?->name ?? null,

                /**
                 * 🏥 Contexto SUS/Hospital
                 */
                'unidade_id' => $user->unidade_id ?? null,
                'setor_id' => $user->setor_id ?? null,

                /**
                 * 📩 Verificação
                 */
                'email_verified_at' => $user->email_verified_at,

                /**
                 * 🕒 Último acesso
                 */
                'ultimo_acesso_em' => $user->ultimo_acesso_em ?? null,
            ] : null,
        ];

        /**
         * ------------------------------------------------------------------
         * 🔔 Flash messages
         * ------------------------------------------------------------------
         */
        $flash = [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
            'warning' => fn () => $request->session()->get('warning'),
            'info' => fn () => $request->session()->get('info'),
            'status' => fn () => $request->session()->get('status'),
        ];

        /**
         * ------------------------------------------------------------------
         * 🛡️ Segurança frontend
         * ------------------------------------------------------------------
         */
        $security = [
            'csrf_token' => csrf_token(),
            'correlation_id' => $correlationId,
            'request_id' => (string) Str::uuid(),
        ];

        /**
         * ------------------------------------------------------------------
         * 📊 Feature flags cacheadas
         * ------------------------------------------------------------------
         */
        $features = Cache::remember(
            'frontend:features',
            now()->addMinutes(10),
            fn () => [
                'auditoria' => true,
                'lgpd' => true,
                'monitoramento' => true,
                'notificacoes' => true,
            ]
        );

        /**
         * ------------------------------------------------------------------
         * 🧠 Auditoria frontend silenciosa
         * ------------------------------------------------------------------
         */
        try {

            Log::channel('frontend')->info('INERTIA_SHARED', [
                'user_id' => $user?->id,
                'ip' => $request->ip(),
                'rota' => $request->path(),
                'correlation_id' => $correlationId,
            ]);

        } catch (\Throwable $e) {
            // nunca quebra frontend
        }

        return [
            ...parent::share($request),

            /**
             * 🌍 App
             */
            'app' => $app,

            /**
             * 🔐 Auth
             */
            'auth' => $auth,

            /**
             * 🔔 Flash
             */
            'flash' => $flash,

            /**
             * 🛡️ Segurança
             */
            'security' => $security,

            /**
             * 📊 Features
             */
            'features' => $features,

            /**
             * ❌ Validation errors
             */
            'errors' => fn () => session('errors')
                ? session('errors')->getBag('default')->getMessages()
                : (object) [],

            /**
             * 🌐 Metadata
             */
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_path' => $request->path(),
                'request_method' => $request->method(),
            ],
        ];
    }
}