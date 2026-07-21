<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login;

use Illuminate\Http\Request;

final class LoginValidator
{
    public function validate(
        Request $request
    ): array {

        return validator(
            $request->all(),
            [

                'email' => [
                    'required',
                    'email',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                ],
            ]
        )->validate();
    }
}