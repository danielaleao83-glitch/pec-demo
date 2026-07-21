<?php

namespace App\Http\Middleware\Core;

use App\Models\Auditoria;
use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'api/login',
        'api/password/*',
    ];

    /**
     * 🔐 override seguro
     */
    protected function tokensMatch($request): bool
    {
        $result = parent::tokensMatch($request);

        if (! $result) {
            $this->registrarFalhaCsrf($request);
        }

        return $result;
    }

    /**
     * 🧠 auditoria protegida (nível produção)
     */
    private function registrarFalhaCsrf(Request $request): void
    {
        try {

            $userId = $request->user()?->id;

            // 🧠 fingerprint mais precisa (reduz falso positivo NAT)
            $fingerprint = $this->fingerprint($request, $userId);

            // 🚫 anti-flood mais robusto
            if (! Cache::add("csrf:fail:{$fingerprint}", true, now()->addSeconds(30))) {
                return;
            }

            $payload = [
                'user_id' => $userId,
                'ip' => $request->ip(),
                'rota' => $request->path(),
                'metodo' => $request->method(),
                'user_agent' => substr((string)$request->userAgent(), 0, 255),
            ];

            $payload['hash_integridade'] = $this->generateHash($payload);

            // 🛡 fail-safe: nunca pode quebrar request
            if (class_exists(Auditoria::class)) {
                Auditoria::create([
                    'user_id' => $userId,
                    'acao' => 'csrf_fail',
                    'modulo' => 'security',
                    'registro_id' => null,
                    'dados_depois' => $payload,
                    'ip' => $payload['ip'],
                    'user_agent' => $payload['user_agent'],
                    'url' => $request->fullUrl(),
                    'metodo_http' => $request->method(),
                    'executado_em' => now(),
                    'hash_integridade' => $payload['hash_integridade'],
                ]);
            }

        } catch (\Throwable $e) {

            Log::warning('Falha auditoria CSRF (SUS)', [
                'erro' => $e->getMessage(),
                'rota' => $request->path(),
            ]);
        }
    }

    /**
     * 🔐 hash forense consistente
     */
    private function generateHash(array $payload): string
    {
        unset($payload['user_agent']); // reduz variabilidade excessiva

        return hash('sha256', json_encode($payload));
    }

    /**
     * 🧬 fingerprint mais realista (hospital/NAT safe)
     */
    private function fingerprint(Request $request, ?int $userId): string
    {
        return md5(
            ($userId ?? 'guest')
            .'|'.$request->ip()
            .'|'.$request->path()
        );
    }
}