<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait HasSensitiveData
{
    // 🔐 Campos que serão criptografados
    protected array $sensitiveFields = [];

    // Campo de versão do registro
    protected string $versionField = 'versao_registro';

    // Nome da tabela de histórico/auditoria
    protected string $auditTable = 'pacientes_historico';

    /**
     * Boot da trait
     */
    public static function bootHasSensitiveDataAudit()
    {
        // Scope global RLS
        static::addGlobalScope('rls', function ($query) {
            $query->where('ativo', true)
                ->where('anonimizado', false);
        });

        // Trigger versionamento + auditoria antes de update
        static::updating(function ($model) {
            $model->incrementVersion();
            $model->auditChanges();
        });
    }

    /**
     * Criptografia automática ao salvar
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->sensitiveFields) && $value !== null) {
            $chave = config('app.chave_criptografia');
            $value = DB::selectOne('SELECT pgp_sym_encrypt(?, ?) AS valor', [$value, $chave])->valor;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Descriptografia automática ao ler
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (in_array($key, $this->sensitiveFields) && $value !== null) {
            $chave = config('app.chave_criptografia');
            $result = DB::selectOne('SELECT pgp_sym_decrypt(?::bytea, ?) AS valor', [$value, $chave]);

            return $result->valor ?? null;
        }

        return $value;
    }

    /**
     * Incrementa a versão do registro
     */
    protected function incrementVersion(): void
    {
        if ($this->versionField && isset($this->{$this->versionField})) {
            $this->{$this->versionField} = $this->{$this->versionField} + 1;
        }
    }

    /**
     * Audita as alterações na tabela de histórico
     */
    protected function auditChanges(): void
    {
        if (! isset($this->auditTable)) {
            return;
        }

        $changes = $this->getDirty();
        if (! $changes) {
            return;
        }

        $userId = Auth::id() ?? null;

        $data = [
            'paciente_id' => $this->id,
            'alterado_por' => $userId,
            'alteracoes' => json_encode($changes, JSON_UNESCAPED_UNICODE),
            'versao' => $this->{$this->versionField} ?? 1,
            'created_at' => now(),
        ];

        DB::table($this->auditTable)->insert($data);
    }

    /**
     * Scope registros ativos e não anonimizados
     */
    public function scopeActive($query)
    {
        return $query->where('ativo', true)
            ->where('anonimizado', false);
    }
}
