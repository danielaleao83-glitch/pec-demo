<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\SISAB\Queue\SisabQueueService;

class SisabController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $traceId = (string) Str::uuid();

        try {
            // 1. VALIDAÇÃO BÁSICA DE SEGURANÇA (PRIMEIRA BARREIRA)
            $validated = $request->validate([
                'mission_class' => 'nullable|string|in:critical,clinical,batch,low',
                'payload'       => 'required|array',
            ]);

            // 2. LIMITE DE TAMANHO (ANTI-ATAQUE / FLOOD PAYLOAD)
            if (count($validated['payload']) > 5000) {
                return response()->json([
                    'status' => 'rejected',
                    'reason' => 'payload_too_large',
                    'trace_id' => $traceId,
                ], 413);
            }

            // 3. SANITIZAÇÃO MÍNIMA (DEFESA CONTRA INPUT SUJO)
            $payload = $this->sanitize($validated['payload']);

            // 4. MISSÃO CLÍNICA PADRÃO SEGURA
            $missionClass = $validated['mission_class'] ?? 'clinical';

            // 5. LOG DE RASTREIO (AUDITORIA INICIAL)
            Log::info('SISAB_REQUEST_RECEIVED', [
                'trace_id' => $traceId,
                'mission_class' => $missionClass,
                'payload_size' => count($payload),
            ]);

            // 6. CHAMADA AO GATEWAY BLINDADO
            $service = app(SisabQueueService::class);

            $result = $service->enqueue(
                payload: $payload,
                missionClass: $missionClass
            );

            // 7. RESPOSTA CONTROLADA (NUNCA EXPOR DADOS SENSÍVEIS)
            return response()->json([
                'status' => $result['status'],
                'trace_id' => $traceId,
                'fingerprint' => $result['fingerprint'] ?? null,
                'pressure' => $result['pressure'] ?? null,
            ], 202);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => 'invalid_request',
                'trace_id' => $traceId,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {

            // 8. LOG CRÍTICO (SEM VAZAR DETALHE INTERNO)
            Log::error('SISAB_CONTROLLER_FAILURE', [
                'trace_id' => $traceId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'internal_error',
                'trace_id' => $traceId,
            ], 500);
        }
    }

    /**
     * 🧼 SANITIZAÇÃO DEFENSIVA (ANTI PAYLOAD MALFORMADO)
     */
    private function sanitize(array $payload): array
    {
        array_walk_recursive($payload, function (&$value) {
            if (is_string($value)) {
                // remove caracteres perigosos básicos
                $value = trim($value);
                $value = preg_replace('/[^\P{C}\n]+/u', '', $value);
            }
        });

        return $payload;
    }
}