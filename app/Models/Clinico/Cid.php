<?php

namespace App\Models\Clinico;

use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Cid extends Model
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'cids';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'codigo',
        'descricao',
        'created_by',
        'updated_by',
        'registro_hash',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // --------------------------------------------------------------------------
    // Boot do model com auditoria governamental
    // --------------------------------------------------------------------------
    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            $model->gerarHashRegistro();
            $model->registrarAcesso(Auth::id(), 'create', 'cids', null, null, $model->attributesToArray());
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            $model->gerarHashRegistro();
            $model->registrarAcesso(Auth::id(), 'update', 'cids', $model->codigo, $model->getOriginal(), $model->getDirty());
        });

        static::deleting(function ($model) {
            $model->registrarAcesso(Auth::id(), 'delete', 'cids', $model->codigo, $model->attributesToArray(), null);
        });
    }

    // --------------------------------------------------------------------------
    // Hash de integridade governamental
    // --------------------------------------------------------------------------
    public function gerarHashRegistro(): void
    {
        $this->registro_hash = hash('sha256', json_encode([
            $this->codigo,
            $this->descricao,
        ]));
    }

    // --------------------------------------------------------------------------
    // Scopes para buscas rápidas
    // --------------------------------------------------------------------------
    public function scopePorCodigo($query, string $codigo)
    {
        return $query->where('codigo', $codigo);
    }

    public function scopePorDescricao($query, string $descricao)
    {
        return $query->where('descricao', 'like', "%{$descricao}%");
    }

    // --------------------------------------------------------------------------
    // Índices recomendados para consultas
    // --------------------------------------------------------------------------
    public static function criarIndices()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('cids')) {
            \Illuminate\Support\Facades\Schema::table('cids', function ($table) {
                $table->index('codigo');
                $table->index('descricao');
            });
        }
    }
}
