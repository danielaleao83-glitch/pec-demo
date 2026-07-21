<?php

namespace App\Infrastructure\Security;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutorizacaoAuditoriaService
{
    /**
     * 📌 Evento de autorização permitida
     */
    public function registrar(array $dados): void
    {
        $this->persistir('AUTORIZACAO', $dados, false);
    }

    /**
     * 🔐 Evento de autorização negada (segurança crítica)
     */
    public function registrarNegado(array $dados): void
    {
        $this->persistir('AUTORIZACAO_NEGADA', $dados, true);
    }

    /**
     * ⚙️ Persistência centralizada
     */
    private function persistir(string $tipo, array $dados, bool $critico = false): void
    {
        try {
            DB::table('eventos_sistema')->insert([
                'id' => (string) Str::uuid(),
                'tipo' => $tipo,
                'descricao' => $this->normalizar($dados),
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        } catch (Throwable $e) {

            Log::log(
                $critico ? 'critical' : 'error',
                'Falha ao registrar evento de autorização',
                [
                    'tipo' => $tipo,
                    'dados' => $dados,
                    'erro' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * 🧼 Normalização consistente
     */
    private function normalizar(array $dados): string
    {
        return json_encode(
            $this->ordenarRecursivo($dados),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '{}';
    }

    /**
     * 🔁 Ordenação profunda determinística
     */
    private function ordenarRecursivo(array $dados): array
    {
        ksort($dados);

        foreach ($dados as $key => $value) {
            if (is_array($value)) {
                $dados[$key] = $this->ordenarRecursivo($value);
            }
        }

        return $dados;
    }
}