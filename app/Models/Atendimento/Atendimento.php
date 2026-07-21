<?php

namespace App\Models\Atendimento;

use App\Models\Estabelecimentos\Unidade;
use App\Models\Paciente\Paciente;
use App\Models\Permissoes\User;
use App\Models\RegistroMultiprofissional\Evolucao;
use App\Models\RegistroMultiprofissional\ExameLaboratorial;
use App\Models\RegistroMultiprofissional\Prescricao;
use App\Models\RegistroMultiprofissional\RegistroMultiprofissional;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Atendimento extends BaseModel
{
    use HasFactory, HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'atendimentos';

    protected $fillable = [
        'uuid',
        'paciente_id',
        'profissional_id',
        'unidade_id',
        'cnes',
        'data_atendimento',
        'tipo_atendimento',
        'prioridade',
        'status',
        'observacoes',
        'senha',
        'guiche_id',
        'hora_inicio',
        'hora_fim',
        'cbo',
        'profissional_tipo',
    ];

    protected $casts = [
        'data_atendimento' => 'datetime',
        'hora_inicio' => 'datetime',
        'hora_fim' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $with = ['paciente', 'profissional', 'unidade'];

    public const STATUS = [
        'aguardando',
        'chamando',
        'em_atendimento',
        'finalizado',
        'cancelado',
    ];

    public const TIPOS = [
        'clinico',
        'enfermagem',
        'psicologico',
        'social',
        'odontologico',
        'domiciliar',
        'teleatendimento',
        'administrativo',
    ];

    public const PRIORIDADES = ['baixa', 'media', 'alta'];

    /*
    |----------------------------------------
    | UUID + INICIALIZAÇÃO SEGURA
    |----------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            $model->validarCamposObrigatorios();
            $model->validarValoresPermitidos();

            $model->senha = self::gerarSenhaSegura();
            $model->status = 'aguardando';

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {

            $model->validarCamposObrigatorios();
            $model->validarValoresPermitidos();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::created(function ($model) {
            $model->enviarNotificacaoSegura();
        });
    }

    /*
    |----------------------------------------
    | NOTIFICAÇÃO (NÃO BLOQUEANTE)
    |----------------------------------------
    */
    protected function enviarNotificacaoSegura(): void
    {
        try {
            $paciente = $this->paciente;

            if (!$paciente || !$paciente->telefone) {
                return;
            }

            $mensagem = "Atendimento registrado. Senha: {$this->senha}";

            app(\App\Services\NotificacaoService::class)
                ->enviar($this->paciente_id, $mensagem, 'normal');

        } catch (\Throwable $e) {
            Log::error('Falha notificação atendimento', [
                'atendimento_id' => $this->id,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    /*
    |----------------------------------------
    | SENHA SEGURA (SEM COUNT)
    |----------------------------------------
    */
    public static function gerarSenhaSegura(): string
    {
        return 'A' . strtoupper(substr((string) Str::uuid(), 0, 4));
    }

    /*
    |----------------------------------------
    | REGRAS DE NEGÓCIO
    |----------------------------------------
    */
    public function chamar(): void
    {
        if ($this->status !== 'aguardando') {
            throw new \Exception('Atendimento não pode ser chamado.');
        }

        $this->update([
            'status' => 'chamando',
            'guiche_id' => auth()->user()->guiche_id ?? null,
        ]);
    }

    public function iniciar(): void
    {
        if ($this->status !== 'chamando') {
            throw new \Exception('Atendimento não pode iniciar.');
        }

        $this->update([
            'status' => 'em_atendimento',
            'hora_inicio' => now(),
        ]);
    }

    public function finalizar(): void
    {
        $this->update([
            'status' => 'finalizado',
            'hora_fim' => now(),
        ]);
    }

    /*
    |----------------------------------------
    | VALIDAÇÃO
    |----------------------------------------
    */
    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'profissional_id' => 'required|exists:users,id',
            'unidade_id' => 'required|exists:unidades,id',
            'data_atendimento' => 'required|date',
            'tipo_atendimento' => 'required|string',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    protected function validarValoresPermitidos(): void
    {
        if (!in_array($this->status, self::STATUS)) {
            throw new \InvalidArgumentException('Status inválido.');
        }

        if (!in_array($this->tipo_atendimento, self::TIPOS)) {
            throw new \InvalidArgumentException('Tipo inválido.');
        }

        if (!in_array($this->prioridade, self::PRIORIDADES)) {
            throw new \InvalidArgumentException('Prioridade inválida.');
        }
    }
}