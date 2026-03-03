<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Http\Controllers;

use Cbox\LaravelHealth\Services\SystemMetricsService;
use Illuminate\Http\JsonResponse;

final class JsonMetricsController
{
    public function __invoke(SystemMetricsService $metricsService): JsonResponse
    {
        return new JsonResponse($metricsService->collect());
    }
}
