<?php

namespace App\Models\Procedimentos;

use App\Models\Clinicos\ProcedimentoPaciente;
use App\Models\Producao\ItemProducao;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Model;
// 🔥 NOVOS IMPORTS
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SigtapProcedimento extends Model
{
    use HasSensitiveDataAudit, SoftDeletes;

    // --------------------------------------------------------------------------
    // CONFIGURAÇÃO BÁSICA
    // --------------------------------------------------------------------------
    protected $table = 'sigtap_procedimentos';

    /**
     * ⚠️ Mantido padrão seguro (NÃO altera PK para não quebrar banco)
     */
    protected $fillable = [
        'codigo',
        'nome',
        'complexidade',
        'tipo_financiamento',
        'ativo',
    ];

    protected $guarded = [
        'id',
        'deleted_at',
    ];

    // --------------------------------------------------------------------------
    // CASTS
    // --------------------------------------------------------------------------
    protected $casts = [
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // --------------------------------------------------------------------------
    // RELACIONAMENTOS (🔥 NOVO - INTEGRAÇÃO REAL)
    // --------------------------------------------------------------------------

    /**
     * Procedimentos realizados no atendimento
     */
    public function procedimentosPacientes()
    {
        return $this->hasMany(ProcedimentoPaciente::class, 'procedimento_id');
    }

    /**
     * Itens de produção SUS (BPA / RAAS)
     */
    public function itensProducao()
    {
        return $this->hasMany(ItemProducao::class, 'procedimento_id');
    }

    // --------------------------------------------------------------------------
    // SCOPES
    // --------------------------------------------------------------------------
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorCodigo($query, string $codigo)
    {
        return $query->where('codigo', $codigo);
    }

    // --------------------------------------------------------------------------
    // VALIDAÇÃO (🔥 NOVO - EVITA SUJEIRA NO BANCO)
    // --------------------------------------------------------------------------
    protected function validar(): void
    {
        if (empty($this->codigo)) {
            throw new \InvalidArgumentException('Código SIGTAP é obrigatório.');
        }

        if (strlen($this->codigo) < 6) {
            throw new \InvalidArgumentException('Código SIGTAP inválido.');
        }

        if (empty($this->nome)) {
            throw new \InvalidArgumentException('Nome do procedimento é obrigatório.');
        }
    }

    // --------------------------------------------------------------------------
    // EVENTOS (AUDITORIA + SEGURANÇA)
    // --------------------------------------------------------------------------
    protected static function booted()
    {
        static::creating(function ($model) {

            $model->validar();

            if (method_exists($model, 'registrarAcesso')) {
                try {
                    $model->registrarAcesso(
                        Auth::id(),
                        'create',
                        $model->getTable(),
                        null,
                        null,
                        $model->attributesToArray()
                    );
                } catch (\Throwable $e) {
                    //
                }
            }
        });

        static::updating(function ($model) {

            $model->validar();

            if (method_exists($model, 'registrarAcesso')) {
                try {
                    $model->registrarAcesso(
                        Auth::id(),
                        'update',
                        $model->getTable(),
                        $model->id,
                        $model->getOriginal(),
                        $model->getDirty()
                    );
                } catch (\Throwable $e) {
                    //
                }
            }
        });

        static::deleting(function ($model) {

            if (method_exists($model, 'registrarAcesso')) {
                try {
                    $model->registrarAcesso(
                        Auth::id(),
                        'delete',
                        $model->getTable(),
                        $model->id,
                        $model->attributesToArray(),
                        null
                    );
                } catch (\Throwable $e) {
                    //
                }
            }
        });
    }
}
