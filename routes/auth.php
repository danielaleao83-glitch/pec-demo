<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;

use App\Services\AuditoriaService;

/*
|--------------------------------------------------------------------------
| 🌐 GUEST ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware([

    'guest',
    'throttle:login',

])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 🔐 LOGIN
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | FORM LOGIN
        |--------------------------------------------------------------------------
        */

        Route::get('/login', [
            AuthenticatedSessionController::class,
            'create',
        ])->name('login');

        /*
        |--------------------------------------------------------------------------
        | LOGIN ACTION
        |--------------------------------------------------------------------------
        */

        Route::post('/login', [
            AuthenticatedSessionController::class,
            'store',
        ])
            ->middleware([
                'throttle:login',
                'firewall',
                'geo.block',
            ])
            ->name('login.perform');

        /*
        |--------------------------------------------------------------------------
        | FORGOT PASSWORD
        |--------------------------------------------------------------------------
        */

        Route::get('/forgot-password', [
            PasswordResetLinkController::class,
            'create',
        ])->name('password.request');

        Route::post('/forgot-password', [
            PasswordResetLinkController::class,
            'store',
        ])
            ->middleware([
                'throttle:6,1',
                'firewall',
            ])
            ->name('password.email');

        /*
        |--------------------------------------------------------------------------
        | RESET PASSWORD
        |--------------------------------------------------------------------------
        */

        Route::get('/reset-password/{token}', [
            NewPasswordController::class,
            'create',
        ])
            ->where('token', '.*')
            ->name('password.reset');

        Route::post('/reset-password', [
            NewPasswordController::class,
            'store',
        ])
            ->middleware([
                'throttle:6,1',
                'firewall',
            ])
            ->name('password.store');
    });
});

/*
|--------------------------------------------------------------------------
| 🔐 AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware([

    'auth',
    'firewall',
    'security.gate',
    'governamental',

])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 📧 EMAIL VERIFICATION
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {

        Route::get('/verify-email', EmailVerificationPromptController::class)
            ->name('verification.notice');

        Route::get('/verify-email/{id}/{hash}', [
            EmailVerificationNotificationController::class,
            'store',
        ])
            ->middleware([
                'signed',
                'throttle:6,1',
            ])
            ->whereNumber('id')
            ->name('verification.verify');

        Route::post('/email/verification-notification', [
            EmailVerificationNotificationController::class,
            'store',
        ])
            ->middleware([
                'throttle:6,1',
            ])
            ->name('verification.send');

        /*
        |--------------------------------------------------------------------------
        | 🔑 CONFIRM PASSWORD
        |--------------------------------------------------------------------------
        */

        Route::get('/confirm-password', [
            ConfirmablePasswordController::class,
            'show',
        ])->name('password.confirm');

        Route::post('/confirm-password', [
            ConfirmablePasswordController::class,
            'store',
        ])
            ->middleware([
                'throttle:10,1',
            ])
            ->name('password.confirm.store');

        /*
        |--------------------------------------------------------------------------
        | 🔒 UPDATE PASSWORD
        |--------------------------------------------------------------------------
        */

        Route::put('/password', [
            PasswordController::class,
            'update',
        ])
            ->middleware([
                'throttle:10,1',
            ])
            ->name('password.update');

        /*
        |--------------------------------------------------------------------------
        | 🚪 LOGOUT
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', function () {

            $user = auth()->user();

            AuditoriaService::registrar(
                'logout',
                'auth',
                $user?->id,
                null,
                [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toISOString(),
                ]
            );

            return app(AuthenticatedSessionController::class)->destroy(request());

        })
            ->middleware([
                'throttle:logout',
            ])
            ->name('logout');
    });
});