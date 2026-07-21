<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * 🏠 Home pós-login
     */
    public const HOME = '/dashboard';

    public function boot(): void
    {
        $this->routes(function () {

            /**
             * 🌐 WEB
             */
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            /**
             * 🔐 API PRINCIPAL
             * (usuário + sistema interno)
             */
            Route::middleware([
                    'api',
                    'throttle:120,1',
                ])
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            /**
             * 🧠 WEBHOOKS (externos)
             * ⚠️ validação deve ser feita no controller/service
             */
            if (file_exists(base_path('routes/webhooks.php'))) {
                Route::middleware([
                        'api',
                        'throttle:60,1',
                    ])
                    ->prefix('webhooks')
                    ->group(base_path('routes/webhooks.php'));
            }

            /**
             * 📡 MONITORAMENTO / HEALTHCHECK
             */
            if (file_exists(base_path('routes/system.php'))) {
                Route::middleware([
                        'api',
                        'throttle:30,1',
                    ])
                    ->prefix('system')
                    ->group(base_path('routes/system.php'));
            }
        });
    }
}