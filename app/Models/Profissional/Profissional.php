<?php

namespace App\Models\Profissional;

use App\Models\Estabelecimentos\Unidade;
use App\Models\Permissoes\User;
use App\Models\Sistema\BaseModel;
use App\Traits\HasSensitiveDataAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Profissional extends BaseModel
{
    use HasSensitiveDataAudit, SoftDeletes;

    protected $table = 'profissionais';

    protected $fillable = [
        'user_id',
        'cns',
        'cbo',
        'cnes',
        'tipo_vinculo',
    ];

    protected $casts = [
        'cns' => 'string',
        'cbo' => 'string',
        'cnes' => 'string',
    ];

    // RELAÇÕES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'cnes', 'cnes');
    }

    // VALIDAÇÃO PADRÃO MINISTÉRIO
    protected static function booted()
    {
        static::creating(function ($model) {

            if (strlen($model->cns) != 15) {
                throw new \InvalidArgumentException('CNS inválido');
            }

            if (strlen($model->cbo) != 6) {
                throw new \InvalidArgumentException('CBO inválido');
            }

            if (strlen($model->cnes) != 7) {
                throw new \InvalidArgumentException('CNES inválido');
            }

            $model->registrarAcesso(
                Auth::id(),
                'create',
                'profissional',
                null,
                null,
                $model->attributesToArray()
            );
        });
    }
}
