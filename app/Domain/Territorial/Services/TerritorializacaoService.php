<?php

declare(strict_types=1);

namespace App\Services\Territorial;

use App\Models\Territorializacao;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TerritorializacaoService
{
    private const CACHE_TTL_MINUTES = 5;

    public function __construct(
        private readonly TerritorializacaoQueryService $queryService,
        private readonly TerritorializacaoCacheService $cacheService,
        private readonly AuditoriaService $auditoriaService,
    ) {
    }

    /**
     * 🗺️ LISTAGEM
     */
    public function index(Request $request): array
    {
        $correlationId = $this->correlationId();

        try {

            $validated = $request->validated();

            $cacheKey = $this->cacheService->indexKey($validated);

            /** @var LengthAwarePaginator $result */
            $result = $this->cacheService->remember(
                key: $cacheKey,
                ttlMinutes: self::CACHE_TTL_MINUTES,
                callback: fn () => $this->queryService->paginate($validated)
            );

            $this->auditoria(
                acao: 'TERRITORIALIZACAO_LISTAGEM',
                dados: [
                    'filters' => $validated,
                    'total' => $result->total(),
                    'correlation_id' => $correlationId,
                ]
            );

            return [
                'status' => 'success',
                'data' => $result->items(),
                'meta' => [
                    'total' => $result->total(),
                    'current_page' => $result->currentPage(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'generated_at' => now()->toISOString(),
                    'correlation_id' => $correlationId,
                ],
            ];

        } catch (Throwable $e) {

            $this->critical(
                action: 'TERRITORIALIZACAO_INDEX_FAIL',
                exception: $e,
                correlationId: $correlationId
            );

            return $this->errorResponse(
                message: 'Erro ao listar territorializações',
                correlationId: $correlationId
            );
        }
    }

    /**
     * 📍 CRIAÇÃO
     */
    public function store(Request $request): array
    {
        $correlationId = $this->correlationId();

        DB::beginTransaction();

        try {

            $validated = $request->validated();

            $exists = Territorializacao::query()
                ->where('municipio_id', $validated['municipio_id'])
                ->where('equipe_id', $validated['equipe_id'])
                ->whereRaw(
                    'LOWER(microarea) = ?',
                    [mb_strtolower(trim($validated['microarea']))]
                )
                ->exists();

            if ($exists) {

                DB::rollBack();

                return [
                    'status' => 'conflict',
                    'message' => 'Microárea já cadastrada para esta equipe',
                    'meta' => [
                        'correlation_id' => $correlationId,
                    ],
                    'http_code' => Response::HTTP_CONFLICT,
                ];
            }

            $territorializacao = Territorializacao::query()->create([
                'municipio_id' => $validated['municipio_id'],
                'equipe_id' => $validated['equipe_id'],
                'microarea' => trim($validated['microarea']),
                'descricao' => $validated['descricao'] ?? null,
                'geo_json' => $validated['geo_json'] ?? null,
            ]);

            DB::commit();

            $this->cacheService->flush();

            $this->auditoria(
                acao: 'TERRITORIALIZACAO_CRIADA',
                dados: [
                    'territorializacao_id' => $territorializacao->id,
                    'municipio_id' => $territorializacao->municipio_id,
                    'equipe_id' => $territorializacao->equipe_id,
                    'correlation_id' => $correlationId,
                ]
            );

            return [
                'status' => 'success',
                'message' => 'Territorialização criada com sucesso',
                'data' => $territorializacao->fresh(),
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
                'http_code' => Response::HTTP_CREATED,
            ];

        } catch (Throwable $e) {

            DB::rollBack();

            $this->critical(
                action: 'TERRITORIALIZACAO_STORE_FAIL',
                exception: $e,
                correlationId: $correlationId
            );

            return $this->errorResponse(
                message: 'Erro ao criar territorialização',
                correlationId: $correlationId
            );
        }
    }

    /**
     * 🔎 DETALHE
     */
    public function show(string $id): array
    {
        $correlationId = $this->correlationId();

        try {

            $territorializacao = Territorializacao::query()
                ->select([
                    'id',
                    'municipio_id',
                    'equipe_id',
                    'microarea',
                    'descricao',
                    'geo_json',
                    'created_at',
                    'updated_at',
                ])
                ->findOrFail($id);

            $this->auditoria(
                acao: 'TERRITORIALIZACAO_VISUALIZADA',
                dados: [
                    'territorializacao_id' => $territorializacao->id,
                    'correlation_id' => $correlationId,
                ]
            );

            return [
                'status' => 'success',
                'data' => $territorializacao,
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
            ];

        } catch (ModelNotFoundException) {

            return [
                'status' => 'not_found',
                'message' => 'Territorialização não encontrada',
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
                'http_code' => Response::HTTP_NOT_FOUND,
            ];

        } catch (Throwable $e) {

            $this->critical(
                action: 'TERRITORIALIZACAO_SHOW_FAIL',
                exception: $e,
                correlationId: $correlationId
            );

            return $this->errorResponse(
                message: 'Erro ao consultar territorialização',
                correlationId: $correlationId
            );
        }
    }

    /**
     * ✏️ UPDATE
     */
    public function update(
        Request $request,
        string $id
    ): array {

        $correlationId = $this->correlationId();

        DB::beginTransaction();

        try {

            $territorializacao = Territorializacao::query()
                ->findOrFail($id);

            $validated = $request->validated();

            $territorializacao->update([
                'microarea' => isset($validated['microarea'])
                    ? trim($validated['microarea'])
                    : $territorializacao->microarea,

                'descricao' => $validated['descricao']
                    ?? $territorializacao->descricao,

                'geo_json' => $validated['geo_json']
                    ?? $territorializacao->geo_json,
            ]);

            DB::commit();

            $this->cacheService->flush();

            $this->auditoria(
                acao: 'TERRITORIALIZACAO_ATUALIZADA',
                dados: [
                    'territorializacao_id' => $territorializacao->id,
                    'correlation_id' => $correlationId,
                ]
            );

            return [
                'status' => 'success',
                'message' => 'Territorialização atualizada com sucesso',
                'data' => $territorializacao->fresh(),
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
            ];

        } catch (ModelNotFoundException) {

            DB::rollBack();

            return [
                'status' => 'not_found',
                'message' => 'Territorialização não encontrada',
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
                'http_code' => Response::HTTP_NOT_FOUND,
            ];

        } catch (Throwable $e) {

            DB::rollBack();

            $this->critical(
                action: 'TERRITORIALIZACAO_UPDATE_FAIL',
                exception: $e,
                correlationId: $correlationId
            );

            return $this->errorResponse(
                message: 'Erro ao atualizar territorialização',
                correlationId: $correlationId
            );
        }
    }

    /**
     * 🗑️ REMOÇÃO LÓGICA
     */
    public function destroy(string $id): array
    {
        $correlationId = $this->correlationId();

        DB::beginTransaction();

        try {

            $territorializacao = Territorializacao::query()
                ->findOrFail($id);

            $territorializacao->delete();

            DB::commit();

            $this->cacheService->flush();

            $this->auditoria(
                acao: 'TERRITORIALIZACAO_REMOVIDA',
                dados: [
                    'territorializacao_id' => $territorializacao->id,
                    'microarea' => $territorializacao->microarea,
                    'correlation_id' => $correlationId,
                ]
            );

            return [
                'status' => 'success',
                'message' => 'Territorialização removida com sucesso',
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
            ];

        } catch (ModelNotFoundException) {

            DB::rollBack();

            return [
                'status' => 'not_found',
                'message' => 'Territorialização não encontrada',
                'meta' => [
                    'correlation_id' => $correlationId,
                ],
                'http_code' => Response::HTTP_NOT_FOUND,
            ];

        } catch (Throwable $e) {

            DB::rollBack();

            $this->critical(
                action: 'TERRITORIALIZACAO_DELETE_FAIL',
                exception: $e,
                correlationId: $correlationId
            );

            return $this->errorResponse(
                message: 'Erro ao remover territorialização',
                correlationId: $correlationId
            );
        }
    }

    /**
     * 🧠 AUDITORIA CENTRAL
     */
    private function auditoria(
        string $acao,
        array $dados = []
    ): void {

        $this->auditoriaService->registrar(
            acao: $acao,
            modulo: 'TERRITORIAL',
            registroId: $dados['territorializacao_id'] ?? null,
            userId: Auth::id(),
            dados: [
                ...$dados,
                'ip' => request()->ip(),
                'user_agent' => substr(
                    (string) request()->userAgent(),
                    0,
                    500
                ),
            ]
        );
    }

    /**
     * 🔥 LOG CRÍTICO
     */
    private function critical(
        string $action,
        Throwable $exception,
        string $correlationId
    ): void {

        Log::critical($action, [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'correlation_id' => $correlationId,
        ]);
    }

    /**
     * ❌ RESPONSE PADRONIZADA
     */
    private function errorResponse(
        string $message,
        string $correlationId
    ): array {

        return [
            'status' => 'error',
            'message' => $message,
            'meta' => [
                'correlation_id' => $correlationId,
            ],
            'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ];
    }

    /**
     * 🧬 CORRELATION ID
     */
    private function correlationId(): string
    {
        return app()->bound('correlation_id')
            ? app('correlation_id')
            : (string) Str::uuid();
    }
}