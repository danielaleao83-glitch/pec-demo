<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login\Actions;

use App\Application\Services\Auth\Login\Validator\LoginValidator;
use Illuminate\Http\Request;

final class ValidateLoginRequestAction
{
    public function __construct(
        private readonly LoginValidator $validator,
    ) {}

    public function execute(
        Request $request
    ): array {

        return $this->validator
            ->validate(
                payload:
                    $request->all()
            );
    }
}