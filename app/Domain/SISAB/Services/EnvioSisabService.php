<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB;

use App\Services\ESusService\SISAB\Auditoria\SisabAuditService;
use App\Services\ESusService\SISAB\Exceptions\SisabException;
use App\Services\ESusService\SISAB\Exceptions\SisabPayloadException;
use App\Services\ESusService\SISAB\Factories\SisabMetadataFactory;
use App\Services\ESusService\SISAB\Integridade\SisabIntegridadeService;
use App\Services\ESusService\SISAB\Locks\SisabLockService;
use App\Services\ESusService\SISAB\Storage\SisabStorageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class EnvioSisabService
{
    public function __construct(
        private readonly SisabStorageService $storage,
        private readonly SisabIntegridadeService $integrity,
        private readonly SisabLockService $lock
    ) {}

    public function enviar(string $xml): array
    {
        $traceId = Str::uuid()->toString();

        /**
         * 🔐 METADATA
         */
        $metadata = SisabMetadataFactory::make([
            'trace_id' => $traceId,
            'environment' => app()->environment(),
            'ip' => request()->ip() ?? 'CLI',
            'user_uuid' => auth()->user()?->uuid,
        ]);

        /**
         * 🔐 HASH BASE
         */
        $hash = $this->integrity->hash($xml);

        /**
         * 🚫 IDEMPOTÊNCIA
         */
        $cacheKey = "sisab:xml:{$hash}";

        if (Cache::has($cacheKey)) {
            SisabAuditService::log('SISAB_DUPLICATE_BLOCK', [
                'trace_id' => $traceId,
                'hash' => $hash,
            ]);

            return [
                'status' => true,
                'duplicated' => true,
                'trace_id' => $traceId,
                'hash' => $hash,
            ];
        }

        /**
         * 🔒 LOCK + EXECUÇÃO
         */
        return $this->lock->execute($hash, function () use (
            $xml,
            $traceId,
            $hash,
            $cacheKey,
            $metadata
        ) {

            try {

                /**
                 * 💾 STORAGE (ÚNICA RESPONSABILIDADE)
                 */
                $result = $this->storage->store(
                    xml: $xml,
                    traceId: $traceId
                );

                /**
                 * 🚫 CACHE IDEMPOTÊNCIA
                 */
                Cache::put(
                    $cacheKey,
                    true,
                    now()->addHours(12)
                );

                /**
                 * 🧾 AUDITORIA
                 */
                SisabAuditService::log('SISAB_XML_STORED', [
                    'trace_id' => $traceId,
                    'xml_uuid' => $result['xml_uuid'],
                    'hash' => $hash,
                    'path' => $result['path'],
                    'size' => $result['size'],
                    'metadata' => $metadata,
                ]);

                return [
                    'status' => true,
                    'trace_id' => $traceId,
                    'xml_uuid' => $result['xml_uuid'],
                    'hash' => $hash,
                    'path' => $result['path'],
                ];

            } catch (Throwable $e) {

                SisabAuditService::log('SISAB_STORE_FAILURE', [
                    'trace_id' => $traceId,
                    'hash' => $hash,
                    'error' => $e->getMessage(),
                ]);

                throw new SisabException(
                    'Falha persistência SISAB',
                    ['trace_id' => $traceId, 'hash' => $hash],
                    $e
                );
            }
        });
    }
}