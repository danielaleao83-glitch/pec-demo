<?php

namespace App\Models\Exames;

use App\Models\Atendimento\Atendimento;
use App\Models\Estabelecimentos\Unidade;
use App\Models\Paciente\Paciente;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExameImagem extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'exames_imagem';

    protected $fillable = [
        'paciente_id',
        'atendimento_id',
        'tipo_imagem',
        'data_exame',
        'resultado_texto',
        'arquivo',
        'unidade_id',
        'assinatura_medico',
    ];

    protected $guarded = [
        'id',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    protected $casts = [
        'data_exame' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tipo_imagem' => 'string',
        'resultado_texto' => 'string',
        'arquivo' => 'string',
        'assinatura_medico' => 'string',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class)->withTrashed();
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class)->withTrashed();
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class)->withTrashed();
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }

            $model->registrarAcesso(
                Auth::id(),
                'create',
                'exame_imagem',
                null,
                null,
                $model->attributesToArray()
            );
        });

        static::updating(function ($model) {
            $model->validarCamposObrigatorios();

            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            $model->registrarAcesso(
                Auth::id(),
                'update',
                'exame_imagem',
                $model->id,
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        static::deleting(function ($model) {
            $model->registrarAcesso(
                Auth::id(),
                'delete',
                'exame_imagem',
                $model->id,
                $model->attributesToArray(),
                null
            );
        });
    }

    protected function validarCamposObrigatorios(): void
    {
        $validator = Validator::make($this->attributesToArray(), [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'atendimento_id' => 'required|integer|exists:atendimentos,id',
            'tipo_imagem' => 'required|string|max:255',
            'data_exame' => 'required|date|before_or_equal:now',
            'resultado_texto' => 'nullable|string|max:2000',
            'arquivo' => 'nullable|string|max:255',
            'unidade_id' => 'required|integer|exists:unidades,id',
            'assinatura_medico' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}
