<?php

namespace App\Http\Middleware\Audit;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Auditoria\Auditoria;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InertiaAuditMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldAudit($request)) {
            return $response;
        }

        try {

            $userId = $request->user()?->id;
            $pacienteId = $this->extrairPaciente($request);
            $modulo = $this->resolverModulo($request);

            $basePayload = [
                'rota' => $request->path(),
                'url' => $request->fullUrl(),
                'metodo' => $request->method(),
                'paciente_id' => $pacienteId,
                'modulo' => $modulo,
                'ip' => $request->ip(),
                'user_agent' => substr((string)$request->userAgent(), 0, 255),
                'user_id' => $userId,
            ];

            // 🔐 hash estável (forense de verdade)
            $basePayload['hash_integridade'] = $this->generateHash($basePayload);

            Auditoria::registrarForense(
                'VIEW',
                $this->fakeModel($request),
                null,
                $basePayload
            );

        } catch (\Throwable $e) {

            Log::warning('Falha auditoria Inertia', [
                'erro' => $e->getMessage(),
                'rota' => $request->path(),
            ]);
        }

        return $response;
    }

    /**
     * 🧠 regra de auditoria
     */
    private function shouldAudit(Request $request): bool
    {
        return $request->isMethod('GET')
            && $request->header('X-Inertia')
            && ! $this->ignorar($request)
            && ! $this->isDuplicado($request);
    }

    /**
     * 🔐 hash consistente (forense real)
     */
    private function generateHash(array $payload): string
    {
        // remove elementos voláteis
        unset($payload['url']);

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 🚫 anti-flood mais realista
     */
    private function isDuplicado(Request $request): bool
    {
        $key = 'audit:inertia:' . $this->fingerprint($request);

        return ! Cache::add($key, true, now()->addSeconds(8));
    }

    private function fingerprint(Request $request): string
    {
        return md5(
            ($request->user()?->id ?? 'guest')
            .'|'.$request->path()
        );
    }

    private function ignorar(Request $request): bool
    {
        return $request->is([
            'sanctum/*',
            'api/*',
            'login',
            'logout',
            '_debugbar/*',
            'horizon/*',
        ]);
    }

    private function extrairPaciente(Request $request): ?string
    {
        return $request->route('paciente')
            ?? $request->route('paciente_id')
            ?? $request->route('id');
    }

    private function resolverModulo(Request $request): string
    {
        return match (true) {
            str_contains($request->path(), 'pacientes') => 'PACIENTE',
            str_contains($request->path(), 'triagem') => 'TRIAGEM',
            str_contains($request->path(), 'atendimento') => 'ATENDIMENTO',
            str_contains($request->path(), 'prescricao') => 'PRESCRICAO',
            str_contains($request->path(), 'visita') => 'VISITA_DOMICILIAR',
            default => 'SISTEMA',
        };
    }

    private function fakeModel(Request $request)
    {
        return new class($request) {
            public function __construct(private $request) {}
            public function getKey() { return $this->request->path(); }
        };
    }
}