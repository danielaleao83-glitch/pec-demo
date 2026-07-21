<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Healthcheck\SystemHealthAggregator;
use Illuminate\Http\JsonResponse;

class HealthcheckController extends Controller
{
    public function __construct(
        private SystemHealthAggregator $health
    ) {}

    public function index(): JsonResponse
    {
        $result = $this->health->check();

        $http = $result['overall_status'] === 'healthy'
            ? 200
            : 503;

        return response()->json([
            'status' => $result['overall_status'],
            'timestamp' => now()->toISOString(),
            'details' => $result,
        ], $http)->withHeaders([
            'Cache-Control' => 'no-store',
        ]);
    }
}