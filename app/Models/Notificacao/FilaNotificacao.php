<?php

namespace App\Models\Notificacao;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FilaNotificacao extends Model
{
    use HasFactory;

    /**
     * Nome da tabela
     */
    protected $table = 'fila_notificacoes';

    /**
     * Primary key UUID
     */
    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Campos permitidos
     */
    protected $fillable = [
        'id',
        'paciente_id',
        'tipo',
        'canal',
        'destino',
        'mensagem',
        'status',
        'tentativas',
        'max_tentativas',
        'erro',
        'agendado_para',
        'enviado_em',
    ];

    /**
     * Casts (nível produção)
     */
    protected $casts = [
        'agendado_para' => 'datetime',
        'enviado_em' => 'datetime',
        'tentativas' => 'integer',
        'max_tentativas' => 'integer',
    ];

    /**
     * Boot UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }

            if (! $model->status) {
                $model->status = 'pendente';
            }

            if (! $model->tentativas) {
                $model->tentativas = 0;
            }

            if (! $model->max_tentativas) {
                $model->max_tentativas = 3;
            }
        });
    }

    /**
     * STATUS helpers
     */
    public function marcarComoEnviado(): void
    {
        $this->update([
            'status' => 'enviado',
            'enviado_em' => now(),
        ]);
    }

    public function marcarComoErro(string $erro): void
    {
        $this->update([
            'status' => 'erro',
            'erro' => $erro,
            'tentativas' => $this->tentativas + 1,
        ]);
    }

    public function podeTentarNovamente(): bool
    {
        return $this->tentativas < $this->max_tentativas;
    }

    public function marcarComoProcessando(): void
    {
        $this->update([
            'status' => 'processando',
        ]);
    }

    /**
     * SCOPES (nível produção hospitalar)
     */
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeFalhas($query)
    {
        return $query->where('status', 'erro');
    }

    public function scopeProcessando($query)
    {
        return $query->where('status', 'processando');
    }

    public function scopeParaEnvio($query)
    {
        return $query->where('status', 'pendente')
            ->where(function ($q) {
                $q->whereNull('agendado_para')
                    ->orWhere('agendado_para', '<=', now());
            });
    }
}
