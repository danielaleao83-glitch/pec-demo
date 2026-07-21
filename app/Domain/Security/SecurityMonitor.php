<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class SecurityMonitor
{
    /*
    |--------------------------------------------------------------------------
    | VERIFICAR ACESSO SUSPEITO GENÉRICO
    |--------------------------------------------------------------------------
    */

    public static function verificarAcessoSuspeito()
    {

        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        $acessos = AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($acessos > 100) {

            self::registrarEvento('acesso_excessivo');

            abort(403, 'Acesso suspeito detectado');

        }

    }

    /*
    |--------------------------------------------------------------------------
    | MONITOR DE ACESSO A PACIENTES
    |--------------------------------------------------------------------------
    */

    public static function verificarAcessoPaciente()
    {

        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        $acessos = AuditLog::where('user_id', $userId)
            ->where('acao', 'visualizar_paciente')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($acessos > 60) {

            self::registrarEvento('acesso_massivo_pacientes');

            abort(403, 'Acesso massivo a pacientes detectado');

        }

    }

    /*
    |--------------------------------------------------------------------------
    | REGISTRAR EVENTO DE SEGURANÇA
    |--------------------------------------------------------------------------
    */

    public static function registrarEvento($evento)
    {

        try {

            AuditLog::create([

                'user_id' => Auth::id(),

                'acao' => $evento,

                'ip' => Request::ip(),

                'user_agent' => Request::userAgent(),

            ]);

        } catch (\Exception $e) {

            Log::warning('Falha ao registrar evento de segurança', [

                'evento' => $evento,

            ]);

        }

    }
}
