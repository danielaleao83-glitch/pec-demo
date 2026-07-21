<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login;

use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class LoginRateLimiter
{
    private const MAX_ATTEMPTS = 5;

    public function ensure(
        string $email,
        ?string $ip
    ): void {

        $key = $this->key($email, $ip);

        if (
            RateLimiter::tooManyAttempts(
                $key,
                self::MAX_ATTEMPTS
            )
        ) {

            abort(
                Response::HTTP_TOO_MANY_REQUESTS,
                'Muitas tentativas.'
            );
        }
    }

    public function hit(
        string $email,
        ?string $ip
    ): void {

        RateLimiter::hit(
            $this->key($email, $ip),
            60
        );
    }

    public function clear(
        string $email,
        ?string $ip
    ): void {

        RateLimiter::clear(
            $this->key($email, $ip)
        );
    }

    private function key(
        string $email,
        ?string $ip
    ): string {

        return sha1(
            strtolower($email)
            . '|'
            . $ip
        );
    }
}