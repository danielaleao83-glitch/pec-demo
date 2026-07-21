<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Logout;

use App\Models\User;

/**
 * =========================================================
 * 🔐 LOGOUT TOKEN REVOKER
 * 🏥 e-SUS APS / DATASUS / RNDS
 * =========================================================
 *
 * ✔ Revogação segura
 * ✔ Sanctum ready
 * ✔ Produção hospitalar
 * ✔ Segurança federal
 * ✔ Anti sessão órfã
 *
 * =========================================================
 */
final class LogoutTokenRevoker
{
    /**
     * =========================================================
     * 🔓 REVOGA TOKEN
     * =========================================================
     */
    public function revoke(
        User $user
    ): void {

        $token = $user->currentAccessToken();

        if (! $token) {
            return;
        }

        $token->delete();
    }

    /**
     * =========================================================
     * 🔥 REVOGA TODOS TOKENS
     * =========================================================
     */
    public function revokeAll(
        User $user
    ): void {

        $user->tokens()
            ->delete();
    }
}