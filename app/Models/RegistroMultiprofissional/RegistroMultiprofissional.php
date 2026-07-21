<?php

namespace App\Models\RegistroMultiprofissional;

use App\Models\Atendimento\Atendimento;
use App\Models\Paciente\Paciente;
use App\Models\Permissoes\User;
use App\Models\Sistema\BaseModel;
use App\Services\WhatsAppService;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegistroMultiprofissional extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'registro_multiprofissional';

    protected $fillable = [
        'atendimento_id',
        'paciente_id',
        'profissional_id',
        'tipo_registro',
        'tipo_atendimento',
        'cbo',
        'registravel_id',
        'registravel_type',
        'descricao',
        'conduta',
        'observacoes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $with = ['profissional', 'paciente'];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function profissional()
    {
        return $this->belongsTo(User::class, 'profissional_id')->withTrashed();
    }

    public function registravel()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | TIPOS CONTROLADOS
    |--------------------------------------------------------------------------
    */

    public const TIPOS = [
        'soap',
        'evolucao',
        'diagnostico',
        'prescricao',
        'exame',
        'encaminhamento',
        'procedimento',
        'visita_domiciliar',
        'saude_mental',
        'vacinacao',
    ];

    public const TIPOS_ATENDIMENTO = [
        'psicologico',
        'social',
        'enfermagem',
        'medico',
    ];

    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($model) {

            $model->validar();

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            $model->registrarAcesso(
                Auth::id(),
                'create',
                'registro_multiprofissional',
                null,
                null,
                $model->attributesToArray()
            );
        });

        static::created(function ($model) {

            if (! $model->paciente || empty($model->paciente->telefone)) {
                return;
            }

            try {
                $whatsapp = app(WhatsAppService::class);

                $mensagens = [
                    'psicologico' => 'Você iniciou acompanhamento psicológico.',
                    'social' => 'Você foi encaminhado para assistência social.',
                    'enfermagem' => 'Você recebeu atendimento de enfermagem.',
                    'medico' => 'Você passou por atendimento médico.',
                ];

                if (isset($mensagens[$model->tipo_atendimento])) {
                    $whatsapp->enviarPaciente(
                        $model->paciente,
                        $mensagens[$model->tipo_atendimento],
                        Auth::id()
                    );
                }

            } catch (\Throwable $e) {
                Log::error('WhatsApp erro registro multiprofissional', [
                    'id' => $model->id,
                    'erro' => $e->getMessage(),
                ]);
            }
        });

        static::updating(function ($model) {

            $model->validar();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            $model->registrarAcesso(
                Auth::id(),
                'update',
                'registro_multiprofissional',
                $model->id,
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        static::deleting(function ($model) {

            if (Auth::check()) {
                $model->registrarAcesso(
                    Auth::id(),
                    'delete',
                    'registro_multiprofissional',
                    $model->id,
                    $model->attributesToArray(),
                    null
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÃO SEGURA
    |--------------------------------------------------------------------------
    */

    protected function validar(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'atendimento_id' => 'required|exists:atendimentos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'profissional_id' => 'required|exists:users,id',
            'tipo_registro' => 'required|in:'.implode(',', self::TIPOS),
            'tipo_atendimento' => 'nullable|in:'.implode(',', self::TIPOS_ATENDIMENTO),
            'registravel_id' => 'required|integer',
            'registravel_type' => 'required|string',
            'descricao' => 'nullable|string|max:2000',
            'conduta' => 'nullable|string|max:2000',
            'observacoes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        // 🔒 proteção real (sem class_exists direto inseguro)
        if (! str_starts_with($this->registravel_type, 'App\\')) {
            throw new \InvalidArgumentException('Tipo de registro inválido.');
        }
    }
}