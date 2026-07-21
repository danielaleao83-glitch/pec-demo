<?php

namespace App\Http\Middleware\Access;

use App\Models\Paciente;
use App\Policies\PacientePolicy;
use App\Services\AuditoriaService;
use App\Services\SecurityMonitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckPacienteAccess
{
    public function handle(Request $request, Closure $next)
    {
        try {

            $user = Auth::user();

            if (! $user) {
                AuditoriaService::registrarSimples('nao_autenticado', $request);
                abort(401);
            }

            $paciente = $this->resolvePaciente($request);

            if ($paciente && ! $this->exists($paciente)) {
                AuditoriaService::registrarSimples('paciente_inexistente', $request);
                abort(404);
            }

            /**
             * 🧠 POLICY CENTRAL (DDD correto)
             */
            if (! app(PacientePolicy::class)->view($user, $paciente)) {

                AuditoriaService::registrarSimples('acesso_negado', $request, $paciente?->id);

                SecurityMonitor::check($user, $request);

                abort(403);
            }

            /**
             * 🏥 LOG DE ACESSO AUTORIZADO (assíncrono idealmente)
             */
            AuditoriaService::registrarSimples(
                'acesso_autorizado',
                $request,
                $paciente?->id
            );

            return $next($request);

        } catch (\Throwable $e) {

            Log::critical('CheckPacienteAccess failure', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            abort(500);
        }
    }

    private function resolvePaciente(Request $request): ?Paciente
    {
        return Paciente::find(
            $request->route('id')
            ?? $request->route('paciente')
        );
    }

    private function exists(?Paciente $paciente): bool
    {
        return $paciente !== null;
    }
}