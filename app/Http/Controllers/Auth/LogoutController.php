<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class LogoutController extends Controller
{
    public function __construct(
        private AuthService $auth
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $requestId = (string) Str::uuid();

        $this->auth->logout($request);

        return response()->json([
            'status' => 'success',
            'request_id' => $requestId,
            'message' => 'logout_ok',
        ]);
    }
}