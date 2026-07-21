<?php

namespace App\Traits;

use App\Models\Auditoria\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    protected static function bootAuditable()
    {
        // 🚫 Evita auditar a própria auditoria
        if (static::class === Auditoria::class) {
            return;
        }

        // CREATE
        static::created(function ($model) {
            self::registrar('create', $model, null, $model->getAttributes());
        });

        // UPDATE
        static::updating(function ($model) {
            $dirty = $model->getDirty();

            // 🚫 Não registra update vazio
            if (empty($dirty)) {
                return;
            }

            self::registrar(
                'update',
                $model,
                $model->getOriginal(),
                $dirty
            );
        });

        // DELETE
        static::deleted(function ($model) {
            self::registrar('delete', $model, $model->getOriginal(), null);
        });
    }

    protected static function registrar($acao, $model, $antes, $depois)
    {
        try {

            // ⚠️ Pode não existir usuário (seed, job, CLI)
            $userId = Auth::id() ?? null;

            Auditoria::create([
                'user_id' => $userId,
                'acao' => $acao,
                'modulo' => strtolower(class_basename($model)),
                'registro_id' => $model->id ?? null,
                'dados_antes' => self::filtrar($antes),
                'dados_depois' => self::filtrar($depois),
            ]);

        } catch (\Throwable $e) {

            // 🔥 NÃO quebra sistema — mas registra erro
            Log::error('Erro auditoria', [
                'erro' => $e->getMessage(),
                'model' => class_basename($model),
                'acao' => $acao,
            ]);
        }
    }

    // 🔐 LGPD - proteção de dados sensíveis
    protected static function filtrar($dados)
    {
        if (! $dados || ! is_array($dados)) {
            return null;
        }

        $sensivel = [
            'password',
            'remember_token',
            'token',
            'cpf',
            'cns',
            'email', // 🔥 opcional (se quiser ocultar)
        ];

        foreach ($sensivel as $campo) {
            if (array_key_exists($campo, $dados)) {
                $dados[$campo] = '***';
            }
        }

        return $dados;
    }
}
