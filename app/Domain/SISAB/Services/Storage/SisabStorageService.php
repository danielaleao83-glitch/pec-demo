<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Storage;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;
use App\Services\ESusService\SISAB\Auditoria\SisabAuditService;
use App\Services\ESusService\SISAB\Auditoria\SisabSecurityLogService;

class SisabStorageService
{
    public function __construct(
        private readonly SisabDirectoryManager $directoryManager,
        private readonly SisabFilePathResolver $resolver,
        private readonly SisabFileWriter $writer
    ) {}

    public function store(string $xml, string $traceId): array
    {
        $hash = hash('sha256', $xml);
        $cacheKey = "sisab:storage:{$hash}";

        if (Cache::has($cacheKey)) {
            return [
                'status' => true,
                'duplicated' => true,
                'hash' => $hash,
                'trace_id' => $traceId,
            ];
        }

        return Cache::lock("sisab:lock:{$hash}", 10)
            ->block(5, function () use ($xml, $traceId, $hash, $cacheKey) {

                try {
                    $this->directoryManager->ensure();

                    $xmlUuid = (string) Str::uuid();

                    $paths = $this->resolver->resolve(
                        $this->directoryManager->path(),
                        $xmlUuid
                    );

                    $this->writer->writeAtomic(
                        $paths['tmp_path'],
                        $xml,
                        $paths['path']
                    );

                    Cache::put($cacheKey, true, now()->addHours(12));

                    SisabAuditService::log('SISAB_XML_STORED', [
                        'trace_id' => $traceId,
                        'xml_uuid' => $xmlUuid,
                        'hash' => $hash,
                        'path' => $paths['path'],
                        'size' => filesize($paths['path']),
                        'timestamp' => now()->toIso8601String(),
                    ]);

                    return [
                        'status' => true,
                        'trace_id' => $traceId,
                        'xml_uuid' => $xmlUuid,
                        'hash' => $hash,
                        'path' => $paths['path'],
                    ];

                } catch (Throwable $e) {

                    SisabSecurityLogService::critical(
                        'SISAB_STORAGE_FAILURE',
                        [
                            'trace_id' => $traceId,
                            'error' => $e->getMessage(),
                            'exception' => get_class($e),
                        ]
                    );

                    throw SisabStorageException::from($e, [
                        'trace_id' => $traceId,
                        'hash' => $hash,
                    ]);
                }
            });
    }
}