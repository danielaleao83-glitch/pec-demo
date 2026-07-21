<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

/*
|--------------------------------------------------------------------------
| CONTROLLERS API
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AuthController;

use App\Http\Controllers\Api\CnesController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\WhatsAppController;

use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\AtendimentoSoapController;
use App\Http\Controllers\DomicilioController;
use App\Http\Controllers\EvolucaoController;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\PacienteAuditoriaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PrescricaoController;
use App\Http\Controllers\SusIntegracaoController;
use App\Http\Controllers\TerritorializacaoController;
use App\Http\Controllers\VisitaDomiciliarController;

use App\Services\AuditoriaService;

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

RateLimiter::for('api', fn (Request $r) =>
    Limit::perMinute(120)->by(
        $r->user()?->id ?? $r->ip()
    )
);

RateLimiter::for('clinico', fn (Request $r) =>
    Limit::perMinute(80)->by(
        $r->user()?->id ?? $r->ip()
    )
);

RateLimiter::for('write-heavy', fn (Request $r) =>
    Limit::perMinute(40)->by(
        $r->user()?->id ?? $r->ip()
    )
);

/*
|--------------------------------------------------------------------------
| 🌐 ROTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 🔐 AUTH
    |--------------------------------------------------------------------------
    */

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware([
            'throttle:auth',
        ])
        ->name('api.login');

    /*
    |--------------------------------------------------------------------------
    | ❤️ HEALTH CHECK
    |--------------------------------------------------------------------------
    */

    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => config('app.name'),
        'timestamp' => now()->toISOString(),
        'laravel' => app()->version(),
    ]))->name('api.health');
});

/*
|--------------------------------------------------------------------------
| 🔐 API V1 PROTEGIDA
|--------------------------------------------------------------------------
*/

Route::prefix('v1')
    ->middleware([
        'auth:sanctum',

        // rate limit
        'throttle:api',

        // segurança
        'firewall',
        'security.gate',
        'geo.block',
        'governamental',

        // auditoria
        'audit.api',
    ])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | 📊 DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('api.dashboard');

        /*
        |--------------------------------------------------------------------------
        | 👤 PACIENTES
        |--------------------------------------------------------------------------
        */

        Route::apiResource('pacientes', PacienteController::class)
            ->middleware([
                'throttle:clinico',
            ])
            ->names('api.pacientes');

        /*
        |--------------------------------------------------------------------------
        | 📜 AUDITORIA PACIENTE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/auditoria/paciente/{id}',
            [PacienteAuditoriaController::class, 'show']
        )
            ->middleware([
                'throttle:clinico',
            ])
            ->whereUuid('id')
            ->name('api.auditoria.paciente');

        /*
        |--------------------------------------------------------------------------
        | 🏥 CNES
        |--------------------------------------------------------------------------
        */

        Route::apiResource('cnes', CnesController::class)
            ->only([
                'index',
                'show',
            ])
            ->middleware([
                'throttle:api',
            ])
            ->names('api.cnes');

        /*
        |--------------------------------------------------------------------------
        | 🏠 DOMICÍLIOS / FAMÍLIAS / TERRITÓRIO
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'throttle:api',
        ])->group(function () {

            Route::apiResource('domicilios', DomicilioController::class)
                ->names('api.domicilios');

            Route::apiResource('familias', FamiliaController::class)
                ->names('api.familias');

            Route::apiResource('territorializacao', TerritorializacaoController::class)
                ->names('api.territorializacao');

            Route::apiResource(
                'visitas-domiciliares',
                VisitaDomiciliarController::class
            )->names('api.visitas');
        });

        /*
        |--------------------------------------------------------------------------
        | 🩺 ATENDIMENTO CLÍNICO
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'throttle:clinico',
        ])->group(function () {

            Route::apiResource('atendimentos', AtendimentoController::class)
                ->names('api.atendimentos');

            /*
            |--------------------------------------------------------------------------
            | SOAP
            |--------------------------------------------------------------------------
            */

            Route::prefix('atendimentos/{atendimento}/soap')
                ->group(function () {

                    Route::get('/', [
                        AtendimentoSoapController::class,
                        'index',
                    ])->name('api.soap.index');

                    Route::post('/', [
                        AtendimentoSoapController::class,
                        'store',
                    ])->name('api.soap.store');
                });

            /*
            |--------------------------------------------------------------------------
            | EVOLUÇÃO
            |--------------------------------------------------------------------------
            */

            Route::prefix('atendimentos/{atendimento}/evolucao')
                ->group(function () {

                    Route::get('/', [
                        EvolucaoController::class,
                        'index',
                    ])->name('api.evolucao.index');

                    Route::post('/', [
                        EvolucaoController::class,
                        'store',
                    ])->name('api.evolucao.store');
                });

            /*
            |--------------------------------------------------------------------------
            | PRESCRIÇÃO
            |--------------------------------------------------------------------------
            */

            Route::prefix('atendimentos/{atendimento}/prescricao')
                ->group(function () {

                    Route::get('/', [
                        PrescricaoController::class,
                        'index',
                    ])->name('api.prescricao.index');

                    Route::post('/', [
                        PrescricaoController::class,
                        'store',
                    ])->name('api.prescricao.store');
                });
        });

        /*
        |--------------------------------------------------------------------------
        | 🏛️ SUS
        |--------------------------------------------------------------------------
        */

        Route::prefix('sus')
            ->middleware([
                'throttle:api',
            ])
            ->group(function () {

                Route::get('/status', [
                    SusIntegracaoController::class,
                    'status',
                ])->name('api.sus.status');

                Route::get('/logs', [
                    SusIntegracaoController::class,
                    'logs',
                ])->name('api.sus.logs');
            });

        /*
        |--------------------------------------------------------------------------
        | 📲 WHATSAPP
        |--------------------------------------------------------------------------
        */

        Route::prefix('whatsapp')
            ->middleware([
                'throttle:write-heavy',
            ])
            ->group(function () {

                Route::post('/enviar', [
                    WhatsAppController::class,
                    'enviar',
                ])->name('api.whatsapp.enviar');
            });

        /*
        |--------------------------------------------------------------------------
        | 🚪 LOGOUT
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', function (Request $request) {

            $user = $request->user();

            if ($user?->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            AuditoriaService::registrar(
                'logout',
                'auth',
                $user?->id
            );

            return response()->json([
                'message' => 'Logout realizado com sucesso',
            ]);
        })->name('api.logout');
    });