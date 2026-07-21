<?php

namespace App\Services\Auth;

use App\Models\Auditoria;
use Illuminate\Support\Str;

class AuthAuditService
{
    public function log(array $data): void
    {
        Auditoria::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $data['user_id'] ?? null,
            'acao' => $data['action'],
            'modulo' => 'auth_rnds',

            'dados_depois' => $data,

            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),

            'hash_integridade' => hash('sha256', json_encode($data) . config('app.key')),
        ]);
    }
}