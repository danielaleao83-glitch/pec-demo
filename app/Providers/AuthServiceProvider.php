<?php

namespace App\Providers;

use App\Models\Atendimento;
use App\Models\AtendimentoSoap;
use App\Models\Paciente;
use App\Policies\AtendimentoPolicy;
use App\Policies\AtendimentoSoapPolicy;
use App\Policies\PacientePolicy;
use App\Services\Auditoria\AutorizacaoAuditoriaService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Paciente::class => PacientePolicy::class,
        Atendimento::class => AtendimentoPolicy::class,
        AtendimentoSoap::class => AtendimentoSoapPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * 🔐 AUDITORIA DE AUTORIZAÇÃO
         * (não deve quebrar fluxo clínico)
         */
        Gate::after(function ($user, $ability, $result, $arguments) {

            if (! $user) {
                return;
            }

            try {

                $model = $arguments[0] ?? null;

                app(AutorizacaoAuditoriaService::class)->registrar([
                    'user_id'  => $user->id,
                    'ability'  => $ability,
                    'permitido'=> (bool) $result,
                    'model'    => is_object($model) ? get_class($model) : null,
                    'model_id' => is_object($model) ? ($model->id ?? null) : null,
                    'ip'       => request()->ip() ?? null,
                ]);

            } catch (\Throwable $e) {
                // 🔒 auditoria nunca pode quebrar atendimento
            }
        });

        /**
         * 🛡️ REGRAS GLOBAIS
         */
        Gate::before(function ($user, $ability) {

            if (! $user) {
                return null;
            }

            // 🔥 ADMIN CONTROLADO (sem bypass total)
            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {

                return in_array($ability, [
                    'viewAny',
                    'view',
                    'create',
                    'update',
                    'delete',
                ], true)
                    ? true
                    : null;
            }

            return null;
        });
    }
}