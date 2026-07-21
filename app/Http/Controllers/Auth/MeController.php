<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'request_id' => (string) Str::uuid(),
            'data' => [
                'user' => $request->user(),
                'authenticated' => $request->user() !== null,
            ],
        ]);
    }
}