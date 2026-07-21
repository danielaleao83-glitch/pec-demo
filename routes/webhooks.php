<?php

use App\Http\Controllers\Api\WhatsAppController;
use App\Http\Controllers\SusIntegracaoController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 🔗 WEBHOOKS EXTERNOS (SUS / e-SUS APS / GOV)
|--------------------------------------------------------------------------
|
| Regras:
| - Stateless
| - Rate limit
| - Auditoria
| - Assinatura HMAC
| - Anti replay
| - Segurança defensiva
|
*/

/*
|--------------------------------------------------------------------------
| 🌐 PREFIXO GLOBAL
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')
    ->middleware([
        'api',
        'throttle:webhooks',
        'firewall',
    ])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | 📲 WHATSAPP
        |--------------------------------------------------------------------------
        */

        Route::post('/whatsapp', [WhatsAppController::class, 'webhook'])
            ->middleware([
                'verify.webhook.signature',
            ])
            ->name('webhooks.whatsapp');

        /*
        |--------------------------------------------------------------------------
        | 🏥 SISAB
        |--------------------------------------------------------------------------
        */

        Route::post('/sisab', [SusIntegracaoController::class, 'webhookSisab'])
            ->middleware([
                'verify.webhook.signature',
            ])
            ->name('webhooks.sisab');

        /*
        |--------------------------------------------------------------------------
        | 🧬 RNDS
        |--------------------------------------------------------------------------
        */

        Route::post('/rnds', [SusIntegracaoController::class, 'webhookRndS'])
            ->middleware([
                'verify.webhook.signature',
            ])
            ->name('webhooks.rnds');

        /*
        |--------------------------------------------------------------------------
        | ❤️ HEALTHCHECK
        |--------------------------------------------------------------------------
        */

        Route::get('/ping', function () {

            return response()->json([

                'status' => 'ok',

                'service' => 'webhooks',

                'environment' => app()->environment(),

                'timestamp' => now()->toISOString(),

            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | 📊 STATUS
        |--------------------------------------------------------------------------
        */

        Route::get('/status', function () {

            return response()->json([

                'webhooks' => true,

                'cache' => config('cache.default'),

                'queue' => config('queue.default'),

                'timestamp' => now()->toISOString(),

            ]);
        });
    });

/*
|--------------------------------------------------------------------------
| 🔐 FALLBACK DE SEGURANÇA
|--------------------------------------------------------------------------
|
| Impede métodos inválidos
|
*/

Route::fallback(function (Request $request) {

    Log::warning('WEBHOOK_ROUTE_NOT_FOUND', [

        'ip' => $request->ip(),

        'method' => $request->method(),

        'url' => $request->fullUrl(),

        'user_agent' => $request->userAgent(),

    ]);

    return response()->json([

        'status' => 'error',

        'message' => 'Webhook endpoint not found',

    ], 404);
});

/*
|--------------------------------------------------------------------------
| 🔐 EXEMPLO DE RATE LIMITER
|--------------------------------------------------------------------------
|
| Adicionar no AppServiceProvider ou RouteServiceProvider
|
| RateLimiter::for('webhooks', function (Request $request) {
|
|     return Limit::perMinute(120)
|         ->by($request->ip());
| });
|
*/

/*
|--------------------------------------------------------------------------
| 🔐 EXEMPLO VERIFY.WEBHOOK.SIGNATURE
|--------------------------------------------------------------------------
|
| Middleware recomendado:
|
| - valida HMAC
| - valida timestamp
| - protege replay
| - valida origem
|
*/

/*
|--------------------------------------------------------------------------
| 🔐 EXEMPLO DE ASSINATURA HMAC
|--------------------------------------------------------------------------
|
| Headers esperados:
|
| X-Signature
| X-Timestamp
|
| hash_hmac(
|     'sha256',
|     $payload.$timestamp,
|     env('WEBHOOK_SECRET')
| );
|
*/

/*
|--------------------------------------------------------------------------
| 🔐 EXEMPLO ANTI-REPLAY
|--------------------------------------------------------------------------
*/

if (! function_exists('webhookReplayProtection')) {

    function webhookReplayProtection(string $signature): bool
    {
        $key = 'webhook:replay:' . sha1($signature);

        if (Cache::has($key)) {
            return false;
        }

        Cache::put($key, true, now()->addMinutes(5));

        return true;
    }
}