<?php

namespace App\Models\Paciente;

use App\Models\Sistema\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PacienteAuditoria extends BaseModel
{
    use SoftDeletes;

    protected $table = 'paciente_auditorias';

    protected $fillable = [
        'user_id',
        'paciente_id',
        'entidade',
        'entidade_id',
        'acao',
        'detalhes',
        'ip',
        'user_agent',
        'nivel',
        'registro_hash',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'user_id' => 'integer',
        'paciente_id' => 'integer',
        'entidade_id' => 'integer',
        'detalhes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // --------------------------------------------------------------------------
    // Relacionamentos
    // --------------------------------------------------------------------------
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // --------------------------------------------------------------------------
    // Registrar auditoria de paciente - blindagem governamental
    // --------------------------------------------------------------------------
    public static function registrar(
        string $acao,
        ?int $pacienteId = null,
        ?string $entidade = null,
        ?int $entidadeId = null,
        array $detalhes = [],
        string $nivel = 'INFO'
    ): self {
        if (! $acao || ! $pacienteId) {
            throw new \InvalidArgumentException('Ação e paciente_id são obrigatórios para auditoria.');
        }

        try {

            $request = request();
            $userId = Auth::id() ?? null;

            // Criptografa detalhes e IP
            $detalhesEncrypted = Crypt::encryptString(json_encode($detalhes));
            $ipEncrypted = Crypt::encryptString($request->ip());

            // Gera hash de integridade do registro
            $registroHash = hash('sha256', json_encode([
                $userId, $pacienteId, $entidade, $entidadeId, $acao, $detalhes, $nivel, $request->ip(), $request->userAgent(),
            ]));

            $auditoria = self::create([
                'user_id' => $userId,
                'paciente_id' => $pacienteId,
                'entidade' => $entidade,
                'entidade_id' => $entidadeId,
                'acao' => $acao,
                'detalhes' => $detalhesEncrypted,
                'ip' => $ipEncrypted,
                'user_agent' => $request->userAgent(),
                'nivel' => $nivel,
                'registro_hash' => $registroHash,
            ]);

            // Cria log forense
            LogForense::create([
                'user_id' => $userId,
                'paciente_id' => $pacienteId,
                'acao' => $acao,
                'dados_anteriores' => null,
                'dados_novos' => $detalhes,
                'rota' => $request->path(),
                'metodo' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $auditoria;

        } catch (\Exception $e) {
            // Log de falhas com hash de erro
            Log::error('Falha ao registrar auditoria do paciente', [
                'acao' => $acao,
                'paciente_id' => $pacienteId,
                'entidade' => $entidade,
                'entidade_id' => $entidadeId,
                'mensagem_erro' => $e->getMessage(),
                'hash_erro' => hash('sha256', $e->getMessage()),
            ]);

            return new self;
        }
    }

    // --------------------------------------------------------------------------
    // Index sugeridos para performance em nível governamental
    // --------------------------------------------------------------------------
    public static function criarIndices()
    {
        if (Schema::hasTable('paciente_auditorias')) {
            Schema::table('paciente_auditorias', function ($table) {
                $table->index('user_id');
                $table->index('paciente_id');
                $table->index('entidade');
                $table->index('entidade_id');
                $table->index('acao');
                $table->index('nivel');
                $table->index(['paciente_id', 'acao', 'created_at'], 'idx_paciente_acao_data');
            });
        }
    }
}
