<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // serviços globais (cache, auditoria, bindings futuros)
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        /**
         * 🔥 Vite apenas em desenvolvimento local
         * Evita dependência de build em produção/staging
         */
        if (app()->environment('local')) {
            Vite::prefetch(concurrency: 3);
        }
    }
}