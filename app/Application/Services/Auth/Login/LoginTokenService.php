<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Ramsey\Uuid\Uuid;

final class LoginTokenService
{
    public function create(
        User $user
    ): NewAccessToken {

        return $user->createToken(
            'sessao-' . Str::uuid()
        );
    }

    public function sessionUuid(): string
    {
        return Uuid::uuid7()->toString();
    }

    public function correlationId(
        Request $request
    ): string {

        return $request->header(
            'X-Correlation-ID'
        ) ?: Uuid::uuid7()->toString();
    }
}