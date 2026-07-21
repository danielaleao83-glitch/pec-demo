<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

final class LoginUserResolver
{
    public function __construct(
        private readonly LoginRateLimiter $rateLimiter,
    ) {}

    public function resolve(
        string $email,
        string $password,
        ?string $ip
    ): User {

        $user =
            User::query()
                ->where(
                    'email',
                    $email
                )
                ->first();

        if (
            ! $user
            || ! Hash::check(
                $password,
                $user->password
            )
        ) {

            $this->rateLimiter->hit(
                email: $email,
                ip: $ip
            );

            throw new AuthenticationException(
                'Credenciais inválidas.'
            );
        }

        $this->rateLimiter->clear(
            email: $email,
            ip: $ip
        );

        return $user;
    }
}