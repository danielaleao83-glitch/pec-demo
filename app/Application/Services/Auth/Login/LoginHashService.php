<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login;

use App\Models\User;

final class LoginHashService
{
    public function generate(
        User $user,
        string $sessionUuid,
        string $correlationId
    ): string {

        return hash(
            'sha512',
            implode('|', [

                $user->id,

                $user->email,

                $sessionUuid,

                $correlationId,

                config('app.key'),
            ])
        );
    }
}