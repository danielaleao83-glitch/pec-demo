<?php

namespace App\Modules\Auditoria\Domain\Entities;

use App\Traits\HasSensitiveDataAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Modules\Auth\Domain\Entities\User;
use App\Modules\Atendimento\Domain\Entities\Atendimento;

use App\Modules\Auditoria\Domain\Enums\AcaoLogProntuarioEnum;

class LogProntuario extends Model
{
    use HasSensitiveDataAudit;
    use SoftDeletes;

    /**
     * --------------------------------------------------------------------------
     * TABELA
     * --------------------------------------------------------------------------
     */
    protected $table = 'log_prontuario';

    /**
     * --------------------------------------------------------------------------
     * PRIMARY KEY
     * --------------------------------------------------------------------------
     */
    protected $primaryKey = 'id';

    /**
     * --------------------------------------------------------------------------
     * MASS ASSIGNMENT
     * --------------------------------------------------------------------------
     */
    protected $fillable = [
        'atendimento_id',
        'acao',
        'usuario_id',
        'ip',
        'user_agent',
        'hash_integridade',
        'hash_anterior',
    ];

    /**
     * --------------------------------------------------------------------------
     * PROTECTED
     * --------------------------------------------------------------------------
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * --------------------------------------------------------------------------
     * CASTS
     * --------------------------------------------------------------------------
     */
    protected $casts = [
        'atendimento_id' => 'integer',
        'usuario_id' => 'integer',
        'acao' => AcaoLogProntuarioEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * --------------------------------------------------------------------------
     * RELATIONSHIPS
     * --------------------------------------------------------------------------
     */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'usuario_id'
        )->withTrashed();
    }

    public function atendimento(): BelongsTo
    {
        return $this->belongsTo(
            Atendimento::class,
            'atendimento_id'
        )->withTrashed();
    }

    /**
     * --------------------------------------------------------------------------
     * MUTATORS
     * --------------------------------------------------------------------------
     */

    public function setIpAttribute(?string $value): void
    {
        if (! $value) {
            return;
        }

        $this->attributes['ip'] = hash_hmac(
            'sha256',
            $value,
            config('app.key')
        );
    }

    public function setUserAgentAttribute(?string $value): void
    {
        if (! $value) {
            return;
        }

        $this->attributes['user_agent'] = strip_tags(
            mb_substr($value, 0, 255)
        );
    }

    /**
     * --------------------------------------------------------------------------
     * HASH DE INTEGRIDADE
     * --------------------------------------------------------------------------
     */

    public function gerarHashIntegridade(): void
    {
        $payload = json_encode([
            'atendimento_id' => $this->atendimento_id,
            'acao' => $this->acao instanceof AcaoLogProntuarioEnum
                ? $this->acao->value
                : $this->acao,
            'usuario_id' => $this->usuario_id,
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
            'created_at' => now()->toISOString(),
            'hash_anterior' => $this->hash_anterior,
        ], JSON_UNESCAPED_UNICODE);

        $this->hash_integridade = hash_hmac(
            'sha512',
            $payload,
            config('app.key')
        );
    }

    /**
     * --------------------------------------------------------------------------
     * APPEND ONLY
     * --------------------------------------------------------------------------
     */

    protected static function booted(): void
    {
        static::creating(function (
            LogProntuario $model
        ): void {

            if (! $model->hash_integridade) {
                $model->gerarHashIntegridade();
            }
        });

        /**
         * Impede alteração forense
         */
        static::updating(function () {
            return false;
        });

        /**
         * Impede exclusão forense
         */
        static::deleting(function () {
            return false;
        });
    }

    /**
     * --------------------------------------------------------------------------
     * SCOPES
     * --------------------------------------------------------------------------
     */

    public function scopePorUsuario(
        Builder $query,
        int $usuarioId
    ): Builder {
        return $query->where(
            'usuario_id',
            $usuarioId
        );
    }

    public function scopePorAtendimento(
        Builder $query,
        int $atendimentoId
    ): Builder {
        return $query->where(
            'atendimento_id',
            $atendimentoId
        );
    }

    public function scopePorAcao(
        Builder $query,
        AcaoLogProntuarioEnum|string $acao
    ): Builder {

        $valor = $acao instanceof AcaoLogProntuarioEnum
            ? $acao->value
            : $acao;

        return $query->where(
            'acao',
            $valor
        );
    }

    public function scopeRecentes(
        Builder $query,
        int $limite = 50
    ): Builder {
        return $query
            ->orderByDesc('created_at')
            ->limit($limite);
    }
}