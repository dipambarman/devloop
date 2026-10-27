<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthCheckController extends Controller
{
    public function __construct(
        protected HealthCheckService $healthCheckService
    ) {}

    /**
     * Handle incoming health check request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $configuredToken = config('services.health.token');
        $providedToken = $request->header('X-Health-Token', $request->query('token'));

        $isAuthorizedForDetails = empty($configuredToken) 
            ? $request->boolean('full') 
            : ($providedToken === $configuredToken);

        $result = $this->healthCheckService->check($isAuthorizedForDetails);

        return response()->json($result['data'], $result['status_code']);
    }
}
