<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;

class ResetPasswordAction
{
    public function execute(array $data): string
    {
        return Password::reset($data, function ($user, $password) {

            $user->password = Hash::make($password);

            // invalida "remember me"
            $user->setRememberToken(Str::random(60));

            $user->save();

            // evento padrão do Laravel (importante para consistência do framework)
            Event::dispatch(new PasswordReset($user));
        });
    }
}