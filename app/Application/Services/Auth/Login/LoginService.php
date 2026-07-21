<?php

declare(strict_types=1);

namespace App\Application\Services\Auth\Login;

use App\Application\Services\Auth\Login\Actions\BuildLoginResponseAction;
use App\Application\Services\Auth\Login\Actions\CreateSessionAction;
use App\Application\Services\Auth\Login\Actions\RegisterLoginAuditAction;
use App\Application\Services\Auth\Login\Actions\ResolveLoginUserAction;
use App\Application\Services\Auth\Login\Actions\StartLoginSecurityContextAction;
use App\Application\Services\Auth\Login\Actions\ValidateCredentialsAction;
use App\Application\Services\Auth\Login\Actions\ValidateLoginRequestAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class LoginService
{
    public function __construct(
        private readonly StartLoginSecurityContextAction $securityContext,
        private readonly ValidateLoginRequestAction $validateRequest,
        private readonly ResolveLoginUserAction $resolveUser,
        private readonly ValidateCredentialsAction $validateCredentials,
        private readonly CreateSessionAction $createSession,
        private readonly RegisterLoginAuditAction $registerAudit,
        private readonly BuildLoginResponseAction $buildResponse,
    ) {}

    public function execute(
        Request $request
    ): array {

        /**
         * 🔐 SECURITY CONTEXT
         */
        $context =
            $this->securityContext
                ->execute($request);

        /**
         * 🔒 VALIDATION
         */
        $credentials =
            $this->validateRequest
                ->execute($request);

        /**
         * 👤 USER
         */
        $user =
            $this->resolveUser
                ->execute(
                    $credentials['email']
                );

        /**
         * 🔐 PASSWORD
         */
        $this->validateCredentials
            ->execute(
                user: $user,
                password:
                    $credentials['password'],
                ip:
                    $request->ip(),
            );

        /**
         * 🏥 ACID
         */
        return DB::transaction(
            function () use (
                $request,
                $context,
                $user
            ) {

                /**
                 * 🔐 SESSION
                 */
                $session =
                    $this->createSession
                        ->execute(
                            user: $user,
                            correlationId:
                                $context['correlation_id'],
                        );

                /**
                 * 🧾 AUDIT
                 */
                $this->registerAudit
                    ->execute(
                        request:
                            $request,

                        user:
                            $user,

                        session:
                            $session,

                        context:
                            $context,
                    );

                /**
                 * 📦 RESPONSE
                 */
                return $this->buildResponse
                    ->execute(
                        user:
                            $user,

                        session:
                            $session,

                        context:
                            $context,
                    );
            }
        );
    }
}