<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login\Actions;

use App\Application\Services\Auth\Login\Resolver\LoginUserResolver;
use App\Models\User;

final class ResolveLoginUserAction
{
    public function __construct(
        private readonly LoginUserResolver $resolver,
    ) {}

    public function execute(
        string $email
    ): ?User {

        return $this->resolver
            ->resolveByEmail(
                $email
            );
    }
}