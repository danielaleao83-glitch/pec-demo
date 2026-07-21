<?php

namespace App\Services\ESusService\SISAB\DTO;

class SisabXmlResult
{
    public function __construct(

        public readonly bool $status,

        public readonly string $xml,

        public readonly string $hash,

        public readonly string $chainHash,

        public readonly string $traceId,

        public readonly ?string $path = null,

        public readonly ?string $canonical = null,

        public readonly array $meta = [],
    ) {}

    /**
     * =========================================================
     * 🧾 ARRAY SERIALIZATION
     * =========================================================
     */
    public function toArray(): array
    {
        return [

            'status' => $this->status,

            'xml' => $this->xml,

            'hash' => $this->hash,

            'chain_hash' => $this->chainHash,

            'trace_id' => $this->traceId,

            'path' => $this->path,

            'canonical' => $this->canonical,

            'meta' => $this->meta,
        ];
    }
}