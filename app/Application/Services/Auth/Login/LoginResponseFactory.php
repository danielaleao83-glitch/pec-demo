<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login;

use App\Models\User;

final class LoginResponseFactory
{
    public function make(
        User $user,
        string $token,
        string $sessionUuid,
        string $correlationId,
        string $hashIntegridade
    ): array {

        return [

            'success' => true,

            'message' => 'Login realizado.',

            'data' => [

                'user' => [

                    'id' => $user->id,

                    'uuid' => $user->uuid,

                    'name' => $user->name,

                    'email' => $user->email,
                ],

                'token' => $token,

                'session_uuid' => $sessionUuid,
            ],

            'meta' => [

                'correlation_id' => $correlationId,

                'hash_integridade' => $hashIntegridade,

                'timestamp' => now()->toIso8601String(),
            ]
        ];
    }
}