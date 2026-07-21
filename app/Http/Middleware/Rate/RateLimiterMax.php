<?php
namespace App\Http\Middleware\Rate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| 🔐 RATE LIMITERS - PRODUÇÃO SUS FEDERAL
|--------------------------------------------------------------------------
|
| Estratégia:
| - isolamento por usuário/IP
| - proteção anti brute force
| - auditoria defensiva
| - limites clínicos separados
| - tolerância para sistemas hospitalares
|
*/

/**
 * 🔑 fingerprint segura
 */
function fingerprint(Request $request): string
{
    return $request->user()?->id
        ? 'user:'.$request->user()->id
        : 'ip:'.$request->ip();
}

/**
 * 🔑 fingerprint auth
 */
function authFingerprint(Request $request): string
{
    return Str::lower(
        $request->ip().'|'.$request->input('email')
    );
}

/*
|--------------------------------------------------------------------------
| 🔐 AUTH
|--------------------------------------------------------------------------
*/

/**
 * LOGIN
 *
 * Proteção anti brute force
 */
RateLimiter::for('login', function (Request $request) {

    return [
        Limit::perMinute(5)
            ->by(authFingerprint($request))
            ->response(function () {

                return response()->json([
                    'message' => 'Muitas tentativas de login.',
                ], 429);
            }),

        // 🚨 trava agressiva por IP
        Limit::perHour(30)
            ->by($request->ip()),
    ];
});

/**
 * LOGOUT
 */
RateLimiter::for('logout', fn (Request $request) =>
    Limit::perMinute(20)
        ->by(fingerprint($request))
);

/**
 * RESET SENHA
 */
RateLimiter::for('password.reset', fn (Request $request) =>
    Limit::perMinute(3)
        ->by(authFingerprint($request))
);

/**
 * EMAIL VERIFY
 */
RateLimiter::for('email.verify', fn (Request $request) =>
    Limit::perMinute(6)
        ->by(fingerprint($request))
);

/*
|--------------------------------------------------------------------------
| 👤 PACIENTES
|--------------------------------------------------------------------------
*/

/**
 * CONSULTA PACIENTE
 */
RateLimiter::for('pacientes', fn (Request $request) =>
    Limit::perMinute(120)
        ->by(fingerprint($request))
);

/**
 * BUSCA CPF/CNS
 */
RateLimiter::for('paciente.search', fn (Request $request) =>
    Limit::perMinute(40)
        ->by(fingerprint($request))
);

/**
 * EXPORTAÇÃO
 */
RateLimiter::for('paciente.export', fn (Request $request) =>
    Limit::perHour(10)
        ->by(fingerprint($request))
);

/*
|--------------------------------------------------------------------------
| 🩺 ATENDIMENTO CLÍNICO
|--------------------------------------------------------------------------
*/

/**
 * SOAP
 */
RateLimiter::for('soap', fn (Request $request) =>
    Limit::perMinute(80)
        ->by(fingerprint($request))
);

/**
 * EVOLUÇÃO
 */
RateLimiter::for('evolucao', fn (Request $request) =>
    Limit::perMinute(80)
        ->by(fingerprint($request))
);

/**
 * PRESCRIÇÃO
 */
RateLimiter::for('prescricao', fn (Request $request) =>
    Limit::perMinute(60)
        ->by(fingerprint($request))
);

/**
 * ASSINATURA CLÍNICA
 */
RateLimiter::for('assinatura', fn (Request $request) =>
    Limit::perMinute(20)
        ->by(fingerprint($request))
);

/*
|--------------------------------------------------------------------------
| 🏥 INTEGRAÇÕES SUS
|--------------------------------------------------------------------------
*/

/**
 * CNES
 */
RateLimiter::for('cnes', fn (Request $request) =>
    Limit::perMinute(100)
        ->by(fingerprint($request))
);

/**
 * CADSUS
 */
RateLimiter::for('cadsus', fn (Request $request) =>
    Limit::perMinute(50)
        ->by(fingerprint($request))
);

/**
 * PEC / e-SUS
 */
RateLimiter::for('esus', fn (Request $request) =>
    Limit::perMinute(80)
        ->by(fingerprint($request))
);

/*
|--------------------------------------------------------------------------
| 📊 AUDITORIA
|--------------------------------------------------------------------------
*/

/**
 * CONSULTA AUDITORIA
 */
RateLimiter::for('auditoria', fn (Request $request) =>
    Limit::perMinute(30)
        ->by(fingerprint($request))
);

/**
 * EXPORT AUDITORIA
 */
RateLimiter::for('auditoria.export', fn (Request $request) =>
    Limit::perHour(5)
        ->by(fingerprint($request))
);

/*
|--------------------------------------------------------------------------
| ❤️ HEALTH CHECK
|--------------------------------------------------------------------------
*/

/**
 * HEALTH
 */
RateLimiter::for('health', fn (Request $request) =>
    Limit::perMinute(120)
        ->by($request->ip())
);

/*
|--------------------------------------------------------------------------
| 🚨 API GLOBAL
|--------------------------------------------------------------------------
|
| Proteção geral da API
|
*/
RateLimiter::for('api', function (Request $request) {

    return [
        Limit::perMinute(300)
            ->by(fingerprint($request)),

        Limit::perHour(5000)
            ->by(fingerprint($request)),
    ];
});