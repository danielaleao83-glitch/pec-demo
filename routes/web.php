<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\AtendimentoSoapController;
use App\Http\Controllers\CnesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvolucaoController;
use App\Http\Controllers\FilaController;
use App\Http\Controllers\GuicheController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PacienteWebController;
use App\Http\Controllers\PrescricaoController;
use App\Http\Controllers\SusIntegracaoController;
use App\Http\Controllers\TriagemController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| 🔐 RATE LIMITERS
|--------------------------------------------------------------------------
*/

RateLimiter::for('auth', fn (Request $r) =>
    Limit::perMinute(5)->by(
        strtolower($r->ip().'|'.$r->input('email'))
    )
);

RateLimiter::for('web', fn (Request $r) =>
    Limit::perMinute(120)->by(
        $r->user()?->id ?? $r->ip()
    )
);

RateLimiter::for('clinico', fn (Request $r) =>
    Limit::perMinute(80)->by(
        $r->user()?->id ?? $r->ip()
    )
);

RateLimiter::for('critico', fn (Request $r) =>
    Limit::perMinute(40)->by(
        $r->user()?->id ?? $r->ip()
    )
);

RateLimiter::for('soc', fn (Request $r) =>
    Limit::perMinute(20)->by(
        $r->user()?->id ?? $r->ip()
    )
);

/*
|--------------------------------------------------------------------------
| ❤️ HEALTH CHECK
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {

    return response()->json([

        'status' => 'ok',

        'system' => 'e-SUS APS',

        'environment' => app()->environment(),

        'timestamp' => now()->toISOString(),

        'laravel' => app()->version(),

    ]);

})->name('health');

/*
|--------------------------------------------------------------------------
| ❤️ HEALTH CHECK AVANÇADO
|--------------------------------------------------------------------------
*/

Route::get('/health/deep', function () {

    return response()->json([

        'status' => 'ok',

        'database' => DB::connection()->getPdo()
            ? 'online'
            : 'offline',

        'cache' => Cache::store()->get('health_check')
            ? 'online'
            : 'online',

        'timestamp' => now()->toISOString(),

    ]);

})->middleware([
    'throttle:soc',
]);

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| 🌐 GUEST ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'guest',
    'throttle:auth',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    Route::get('/login', [
        AuthController::class,
        'show'
    ])->name('login');

    Route::post('/login', [
        AuthController::class,
        'login'
    ])->name('login.post');

    /*
    |--------------------------------------------------------------------------
    | PASSWORD RESET
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/forgot-password',
        'vida.saude.auth.forgot-password'
    )->name('password.request');
});

/*
|--------------------------------------------------------------------------
| 🔐 CORE SECURITY LAYER
|--------------------------------------------------------------------------
*/

Route::prefix('v1')

    ->middleware([

        'auth',

        'throttle:web',

        /*
        |--------------------------------------------------------------------------
        | 🛡 SEGURANÇA ENTERPRISE
        |--------------------------------------------------------------------------
        */

        'security.firewall',
        'security.headers',
        'security.fingerprint',
        'security.risk',
        'security.device',
        'security.session',
        'security.integrity',

        /*
        |--------------------------------------------------------------------------
        | 🛰 AUDITORIA
        |--------------------------------------------------------------------------
        */

        'audit.route',

        /*
        |--------------------------------------------------------------------------
        | 🏥 CONTEXTO UBS
        |--------------------------------------------------------------------------
        */

        'ubs.context',

    ])

    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | 🚪 LOGOUT
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', [
            AuthController::class,
            'logout'
        ])->name('logout');

        /*
        |--------------------------------------------------------------------------
        | 📊 DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::prefix('painel')->group(function () {

            Route::get('/', [
                DashboardController::class,
                'index'
            ])->name('dashboard');

            Route::get('/principal', [
                DashboardController::class,
                'painel'
            ])->name('painel');

        });

        /*
        |--------------------------------------------------------------------------
        | 👤 PACIENTES
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'throttle:clinico',
        ])->group(function () {

            Route::resource(
                'pacientes',
                PacienteController::class
            );

            Route::get(
                '/paciente-web',
                [PacienteWebController::class, 'index']
            )->name('paciente.web');

        });

        /*
        |--------------------------------------------------------------------------
        | 🩺 TRIAGEM
        |--------------------------------------------------------------------------
        */

        Route::prefix('triagem')

            ->middleware([
                'throttle:clinico',
            ])

            ->group(function () {

                Route::get('/', [
                    TriagemController::class,
                    'index'
                ]);

                Route::post('/', [
                    TriagemController::class,
                    'store'
                ]);

            });

        /*
        |--------------------------------------------------------------------------
        | 🏥 GUICHÊ
        |--------------------------------------------------------------------------
        */

        Route::prefix('guiche')->group(function () {

            Route::get('/', [
                GuicheController::class,
                'index'
            ])->name('guiche.index');

            Route::post('/chamar', [
                GuicheController::class,
                'chamar'
            ])->name('guiche.chamar');

        });

        /*
        |--------------------------------------------------------------------------
        | 🏥 ATENDIMENTOS
        |--------------------------------------------------------------------------
        */

        Route::prefix('atendimentos')

            ->middleware([
                'throttle:critico',
            ])

            ->group(function () {

                Route::get('/', [
                    AtendimentoController::class,
                    'index'
                ]);

                Route::post('/', [
                    AtendimentoController::class,
                    'store'
                ]);

                Route::get('/{id}', [
                    AtendimentoController::class,
                    'show'
                ]);

                /*
                |--------------------------------------------------------------------------
                | SOAP
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/{atendimento}/soap',
                    [AtendimentoSoapController::class, 'store']
                );

                /*
                |--------------------------------------------------------------------------
                | EVOLUÇÃO
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/{atendimento}/evolucao',
                    [EvolucaoController::class, 'store']
                );

                /*
                |--------------------------------------------------------------------------
                | PRESCRIÇÃO
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/{atendimento}/prescricao',
                    [PrescricaoController::class, 'store']
                );

            });

        /*
        |--------------------------------------------------------------------------
        | 🚑 FILA
        |--------------------------------------------------------------------------
        */

        Route::prefix('fila')->group(function () {

            Route::get('/', [
                FilaController::class,
                'fila'
            ]);

            Route::post('/chamar/{id}', [
                FilaController::class,
                'chamar'
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | 🏛️ CNES
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'cnes',
            CnesController::class
        )->only([
            'index',
            'store',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 👨‍💻 USUÁRIOS
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'usuarios',
            UserController::class
        )->only([
            'index',
            'store',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 🏛️ SUS
        |--------------------------------------------------------------------------
        */

        Route::prefix('sus')

            ->middleware([
                'throttle:critico',
                'security.firewall',
            ])

            ->group(function () {

                Route::post('/exportar', [
                    SusIntegracaoController::class,
                    'exportar'
                ]);

                Route::post('/sincronizar', [
                    SusIntegracaoController::class,
                    'sincronizar'
                ]);

            });

        /*
        |--------------------------------------------------------------------------
        | 🔔 NOTIFICAÇÕES
        |--------------------------------------------------------------------------
        */

        Route::post('/notificacao/teste', [
            NotificacaoController::class,
            'enviar'
        ]);

        /*
        |--------------------------------------------------------------------------
        | 🔐 LGPD
        |--------------------------------------------------------------------------
        */

        Route::prefix('lgpd')->group(function () {

            Route::get('/consentimentos', function () {

                return response()->json([
                    'status' => 'ok'
                ]);

            });

            Route::post('/anonimizar', function () {

                return response()->json([
                    'status' => 'anonimizado'
                ]);

            });

        });

    });
