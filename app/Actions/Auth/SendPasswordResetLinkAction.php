<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class SendPasswordResetLinkAction
{
    public function execute(string $email): string
    {
        $email = $this->normalizeEmail($email);

        return Password::sendResetLink([
            'email' => $email,
        ]);
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }
}