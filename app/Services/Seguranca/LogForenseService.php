<?php

declare(strict_types=1);

namespace App\Services\Seguranca;

use App\Models\Seguranca\LogForense;
use Illuminate\Support\Facades\Auth;

final class LogForenseService
{
    public function registrar(
        string $evento,
        array $payload = []
    ): void {

        LogForense::create([

            'uuid' => \Str::uuid()->toString(),

            'evento' => $evento,

            'payload' => $payload,

            'user_id' => Auth::id(),

            'ip' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'session_id' => session()->getId(),

            'executado_em' => now(),
        ]);
    }
}