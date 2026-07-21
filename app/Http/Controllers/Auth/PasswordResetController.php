<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function sendLink(Request $request): JsonResponse
    {
        $requestId = (string) Str::uuid();

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink($request->only('email'));

        return response()->json([
            'status' => 'success',
            'request_id' => $requestId,
            'message' => 'reset_link_sent',
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $requestId = (string) Str::uuid();

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->password = bcrypt(request('password'));
                $user->save();
            }
        );

        return response()->json([
            'status' => $status === Password::PASSWORD_RESET ? 'success' : 'error',
            'request_id' => $requestId,
            'message' => $status,
        ]);
    }
}