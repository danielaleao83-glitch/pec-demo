<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /*
    |----------------------------------------------------------------------
    | 🌍 GLOBAL MIDDLEWARE
    |----------------------------------------------------------------------
    */
    protected $middleware = [

        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,

        // 🔐 segurança base
        \App\Http\Middleware\SecurityHeaders::class,
        \App\Http\Middleware\SecurityFirewall::class,

        // 🧼 sanitização
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,

        // ⚙️ sistema
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,

        // 🚦 proteção leve
        \App\Http\Middleware\RateLimiterMax::class,
    ];

    /*
    |----------------------------------------------------------------------
    | 🧠 GROUPS
    |----------------------------------------------------------------------
    */
    protected $middlewareGroups = [

        'web' => [

            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,

            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,

            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // 🔐 segurança leve
            \App\Http\Middleware\SecurityMiddleware::class,

            // 🔍 auditoria
            'api.audit',
        ],

        'api' => [

            // 🚦 rate limit oficial
            'throttle:api',

            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // 🔐 segurança API
            \App\Http\Middleware\SecurityMiddleware::class,
            \App\Http\Middleware\ProtecaoAtaque::class,

            // 🔥 auditoria automática
            'api.audit',

            // 🚨 NOVO → MOTOR DE RISCO
            'audit.risk',
        ],
    ];

    /*
    |----------------------------------------------------------------------
    | 🎯 ROUTE MIDDLEWARE
    |----------------------------------------------------------------------
    */
    protected $routeMiddleware = [

        // 🔐 AUTH
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // 🧠 PERMISSÕES
        'role' => \App\Http\Middleware\CheckRole::class,
        'permission' => \App\Http\Middleware\CheckPermission::class,

        // 🔐 SEGURANÇA
        'firewall' => \App\Http\Middleware\SecurityFirewall::class,
        'protege.api' => \App\Http\Middleware\ProtecaoAtaque::class,

        // 📊 LOG
        'log.acesso' => \App\Http\Middleware\LogAcessoMiddleware::class,
        'log.paciente' => \App\Http\Middleware\LogAcessoPaciente::class,

        // 🚦 RATE
        'rate.max' => \App\Http\Middleware\RateLimiterMax::class,

        // 🔥 AUDITORIA
        'api.audit' => \App\Http\Middleware\ApiAuditMiddleware::class,

        // 🚨 NOVOS
        'audit.risk' => \App\Http\Middleware\AuditoriaRiskMiddleware::class,
        'blocked' => \App\Http\Middleware\CheckBlockedUser::class,
    ];
}