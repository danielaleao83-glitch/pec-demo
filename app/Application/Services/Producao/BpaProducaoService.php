<?php

declare(strict_types=1);

namespace App\Services\Producao;

use App\Models\Atendimento\Atendimento;
use App\Services\Auditoria\AuditoriaService;
use App\Services\Integracoes\BPAOficialService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BpaProducaoService
{
    public function __construct(
        private readonly BPAOficialService $bpaService,
        private readonly AuditoriaService $auditoriaService,
    ) {
    }

    /**
     * 📄 GERAÇÃO BPA DATASUS
     */
    public function gerarBpa(array $data): array
    {
        $correlationId = (string) Str::uuid();

        try {

            $atendimento = Atendimento::query()
                ->with([
                    'procedimentos.procedimento',
                    'paciente',
                    'profissional',
                    'unidade',
                ])
                ->findOrFail(
                    $data['atendimento_id']
                );

            if (!method_exists($atendimento, 'gerarProducao')) {
                throw new \RuntimeException(
                    'Método gerarProducao não implementado.'
                );
            }

            $producao = $atendimento->gerarProducao(
                $data['competencia']
            );

            if (!$producao) {
                throw new \RuntimeException(
                    'Falha ao gerar produção.'
                );
            }

            $arquivo = $this->bpaService->gerarArquivo(
                $producao
            );

            if (blank($arquivo)) {
                throw new \RuntimeException(
                    'Arquivo BPA vazio.'
                );
            }

            $nome = sprintf(
                'bpa_oficial_%s_%s.txt',
                $producao->id,
                now()->format('Ymd_His')
            );

            Storage::disk('local')->put(
                'bpa/' . $nome,
                $arquivo
            );

            $this->auditoriaService->registrar(
                acao: 'BPA_GERADO',
                modulo: 'PRODUCAO',
                registroId: $producao->id,
                userId: auth()->id(),
                dados: [
                    'arquivo' => $nome,
                    'competencia' => $data['competencia'],
                    'correlation_id' => $correlationId,
                ]
            );

            return [
                'status' => 'success',
                'message' => 'BPA gerado com sucesso.',
                'data' => [
                    'arquivo' => $nome,
                    'download_url' => route(
                        'producao.download',
                        ['arquivo' => $nome]
                    ),
                ],
                'meta' => [
                    'correlation_id' => $correlationId,
                    'generated_at' => now()->toISOString(),
                ],
            ];

        } catch (Throwable $e) {

            Log::critical('BPA_GERACAO_ERRO', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'correlation_id' => $correlationId,
            ]);

            return [
                'status' => 'error',
                'message' => 'Erro ao gerar BPA.',
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
            ];
        }
    }

    /**
     * 📥 DOWNLOAD BPA
     */
    public function download(
        string $arquivo
    ): StreamedResponse {

        $path = 'bpa/' . basename($arquivo);

        abort_unless(
            Storage::disk('local')->exists($path),
            404,
            'Arquivo não encontrado.'
        );

        return Storage::disk('local')->download($path);
    }
}