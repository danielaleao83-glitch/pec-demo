<?php

namespace App\Services\LGPD;

use App\Models\Paciente\Paciente;
use App\Services\Auditoria\AutorizacaoAuditoriaService;
use Illuminate\Support\Facades\Auth;

class LGPDService
{
    public function podeAcessar(Paciente $paciente, string $acao = 'view'): bool
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | 🚫 SEM USUÁRIO
        |--------------------------------------------------------------------------
        */
        if (! $user) {
            $this->auditar(false, $acao, $paciente);

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | 🔐 ADMIN CONTROLADO (NÃO TOTAL)
        |--------------------------------------------------------------------------
        */
        if ($user->is_admin ?? false) {

            // Admin NÃO acessa prontuário automaticamente
            if ($acao === 'admin') {
                $this->auditar(true, $acao, $paciente);

                return true;
            }

            $this->auditar(false, $acao, $paciente);

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | 🔵 MESMA EQUIPE
        |--------------------------------------------------------------------------
        */
        if (
            $user->equipe_id &&
            $paciente->equipe_id &&
            $user->equipe_id === $paciente->equipe_id
        ) {
            return $this->permitir($acao, $paciente);
        }

        /*
        |--------------------------------------------------------------------------
        | 🟢 MICROÁREA (ACS)
        |--------------------------------------------------------------------------
        */
        if (
            $user->hasRole('acs') &&
            $user->microarea_id &&
            $paciente->microarea_id &&
            $user->microarea_id === $paciente->microarea_id
        ) {
            return $this->permitir($acao, $paciente);
        }

        /*
        |--------------------------------------------------------------------------
        | 🧠 VÍNCULO CLÍNICO (ATENDIMENTO)
        |--------------------------------------------------------------------------
        */
        if ($this->temVinculoClinico($user->id, $paciente->id)) {
            return $this->permitir($acao, $paciente);
        }

        /*
        |--------------------------------------------------------------------------
        | ❌ NEGADO
        |--------------------------------------------------------------------------
        */
        $this->auditar(false, $acao, $paciente);

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 VERIFICA VÍNCULO CLÍNICO
    |--------------------------------------------------------------------------
    */
    private function temVinculoClinico(int $userId, int $pacienteId): bool
    {
        return \DB::table('atendimentos')
            ->where('paciente_id', $pacienteId)
            ->where('profissional_id', $userId)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ PERMITIR + AUDITAR
    |--------------------------------------------------------------------------
    */
    private function permitir(string $acao, Paciente $paciente): bool
    {
        $this->auditar(true, $acao, $paciente);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | 📊 AUDITORIA AUTOMÁTICA
    |--------------------------------------------------------------------------
    */
    private function auditar(bool $permitido, string $acao, Paciente $paciente): void
    {
        try {
            app(AutorizacaoAuditoriaService::class)->registrar([
                'user_id' => Auth::id(),
                'acao' => $acao,
                'permitido' => $permitido,
                'paciente_id' => $paciente->id,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // nunca quebra o sistema por auditoria
        }
    }
}
