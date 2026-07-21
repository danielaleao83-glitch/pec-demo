<?php

namespace App\Http\Middleware\Security;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityGateway
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $ip = $request->ip();
        $agent = $request->userAgent();

        $sessionId = session()->getId();
        $deviceId = $this->deviceFingerprint($request);

        /*
        |--------------------------------------------
        | 🚨 1. BLOQUEIO DE IP SUSPEITO
        |--------------------------------------------
        */
        if ($this->isBlockedIp($ip)) {
            abort(403, 'Access denied');
        }

        /*
        |--------------------------------------------
        | 🚨 2. DETECÇÃO DE REPLAY / ABUSO
        |--------------------------------------------
        */
        $rateKey = "sec:rate:{$ip}";

        if (Cache::has($rateKey)) {
            Cache::increment($rateKey);
        } else {
            Cache::put($rateKey, 1, 60);
        }

        if (Cache::get($rateKey) > 200) {
            $this->blockIp($ip);
            abort(429);
        }

        /*
        |--------------------------------------------
        | 🔐 3. DEVICE BINDING (sessão blindada)
        |--------------------------------------------
        */
        if ($user) {
            $sessionKey = "device:{$user->id}";

            $storedDevice = Cache::get($sessionKey);

            if ($storedDevice && $storedDevice !== $deviceId) {

                Auth::logout();

                abort(401, 'Sessão inválida');
            }

            Cache::put($sessionKey, $deviceId, 3600);
        }

        /*
        |--------------------------------------------
        | 🧬 CONTEXTO GLOBAL
        |--------------------------------------------
        */
        app()->instance('security_context', [
            'ip' => $ip,
            'device' => $deviceId,
            'session' => $sessionId,
            'user_id' => $user?->id,
            'correlation_id' => (string) Str::uuid(),
        ]);

        return $next($request);
    }

    private function deviceFingerprint(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->ip(),
            $request->userAgent(),
            $request->header('Accept-Language'),
        ]));
    }

    private function isBlockedIp(string $ip): bool
    {
        return Cache::has("blocked_ip:{$ip}");
    }

    private function blockIp(string $ip): void
    {
        Cache::put("blocked_ip:{$ip}", true, 3600);
    }
}