<?php

namespace App\Services\Core\Security;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SusSecurityService
{
    /**
     * 🔐 HASH GLOBAL DE INTEGRIDADE
     */
    public function hash(array $data): string
    {
        return hash('sha256', json_encode($data));
    }

    /**
     * 🧬 fingerprint hospitalar (anti fraude)
     */
    public function fingerprint(): string
    {
        return hash('sha256',
            request()->ip() .
            request()->userAgent() .
            auth()->id()
        );
    }

    /**
     * 🧾 AUDITORIA FEDERAL IMUTÁVEL
     */
    public function audit(string $action, array $payload = []): void
    {
        Log::channel('audit')->info('SUS_AUDIT', [
            'audit_id' => (string) Str::uuid(),
            'action' => $action,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'fingerprint' => $this->fingerprint(),
            'payload_hash' => $this->hash($payload),
            'payload' => $payload,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * 🚨 DETECÇÃO SIMPLES DE RISCO
     */
    public function isRisk(): bool
    {
        return empty(auth()->id()) && request()->ip();
    }
}