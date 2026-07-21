<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| 📡 SYSTEM MONITORING - PADRÃO SUS / HOSPITALAR
|--------------------------------------------------------------------------
|
| Endpoints públicos seguros para:
| - Kubernetes
| - Docker
| - Load Balancer
| - Prometheus
| - Uptime Robot
| - Monitoramento governamental
|
| ⚠️ Nunca expor:
| - secrets
| - tokens
| - stack traces
| - SQL
| - paths internos
|
*/

/*
|--------------------------------------------------------------------------
| ❤️ HEALTH CHECK
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {

    return response()->json([

        'status' => 'ok',

        'service' => config('app.name'),

        'environment' => app()->environment(),

        'timestamp' => now()->toISOString(),

        'timezone' => config('app.timezone'),

        'laravel' => app()->version(),

        'php' => PHP_VERSION,

    ], 200);
});

/*
|--------------------------------------------------------------------------
| 🗄 DATABASE CHECK
|--------------------------------------------------------------------------
*/

Route::get('/db-check', function () {

    try {

        DB::connection()->getPdo();

        $database = DB::select('SELECT 1');

        return response()->json([

            'status' => 'ok',

            'database' => config('database.default'),

            'connection' => !empty($database)
                ? 'online'
                : 'unstable',

            'timestamp' => now()->toISOString(),

        ], 200);

    } catch (\Throwable $e) {

        Log::critical('DB_CHECK_FAILED', [

            'error' => $e->getMessage(),

            'timestamp' => now()->toISOString(),

        ]);

        return response()->json([

            'status' => 'error',

            'database' => 'offline',

            'timestamp' => now()->toISOString(),

        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| ⚡ CACHE CHECK
|--------------------------------------------------------------------------
*/

Route::get('/cache-check', function () {

    try {

        $key = 'healthcheck:' . now()->timestamp;

        Cache::put($key, true, 10);

        $exists = Cache::has($key);

        return response()->json([

            'status' => $exists ? 'ok' : 'error',

            'cache_driver' => config('cache.default'),

            'cache' => $exists ? 'online' : 'offline',

            'timestamp' => now()->toISOString(),

        ], $exists ? 200 : 500);

    } catch (\Throwable $e) {

        Log::critical('CACHE_CHECK_FAILED', [

            'error' => $e->getMessage(),

        ]);

        return response()->json([

            'status' => 'error',

            'cache' => 'offline',

        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| 💾 STORAGE CHECK
|--------------------------------------------------------------------------
*/

Route::get('/storage-check', function () {

    try {

        $disk = Storage::disk('public');

        $testFile = 'healthcheck.txt';

        $disk->put($testFile, now()->toISOString());

        $exists = $disk->exists($testFile);

        if ($exists) {
            $disk->delete($testFile);
        }

        return response()->json([

            'status' => $exists ? 'ok' : 'error',

            'storage' => $exists ? 'online' : 'offline',

            'disk' => 'public',

            'timestamp' => now()->toISOString(),

        ], $exists ? 200 : 500);

    } catch (\Throwable $e) {

        Log::critical('STORAGE_CHECK_FAILED', [

            'error' => $e->getMessage(),

        ]);

        return response()->json([

            'status' => 'error',

            'storage' => 'offline',

        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| 🧠 SYSTEM INFO
|--------------------------------------------------------------------------
|
| Apenas informações seguras
|
*/

Route::get('/version', function () {

    return response()->json([

        'application' => config('app.name'),

        'version' => env('APP_VERSION', '1.0.0'),

        'environment' => app()->environment(),

        'laravel' => app()->version(),

        'php' => PHP_VERSION,

        'timezone' => config('app.timezone'),

        'locale' => config('app.locale'),

    ]);
});

/*
|--------------------------------------------------------------------------
| 🔐 SECURITY STATUS
|--------------------------------------------------------------------------
*/

Route::get('/security-status', function () {

    return response()->json([

        'https' => request()->secure(),

        'debug_mode' => config('app.debug'),

        'sanctum' => class_exists(\Laravel\Sanctum\Sanctum::class),

        'cache_enabled' => app()->configurationIsCached(),

        'routes_cached' => app()->routesAreCached(),

        'maintenance_mode' => app()->isDownForMaintenance(),

        'timestamp' => now()->toISOString(),

    ]);
});

/*
|--------------------------------------------------------------------------
| 📊 READY CHECK
|--------------------------------------------------------------------------
|
| Kubernetes readinessProbe
|
*/

Route::get('/ready', function () {

    try {

        DB::connection()->getPdo();

        Cache::put('ready_check', true, 5);

        return response()->json([

            'ready' => true,

            'timestamp' => now()->toISOString(),

        ]);

    } catch (\Throwable $e) {

        return response()->json([

            'ready' => false,

        ], 503);
    }
});

/*
|--------------------------------------------------------------------------
| 🫀 LIVE CHECK
|--------------------------------------------------------------------------
|
| Kubernetes livenessProbe
|
*/

Route::get('/live', function () {

    return response()->json([

        'alive' => true,

        'timestamp' => now()->toISOString(),

    ]);
});