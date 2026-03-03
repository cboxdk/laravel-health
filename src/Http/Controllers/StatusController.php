<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Http\Controllers;

use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Cbox\LaravelHealth\Services\SystemMetricsService;
use Illuminate\Http\JsonResponse;

final class StatusController
{
    public function __invoke(HealthCheckRunner $runner, SystemMetricsService $metricsService): JsonResponse
    {
        $liveness = $runner->run(EndpointType::Liveness);
        $readiness = $runner->run(EndpointType::Readiness);

        $systemMetrics = $metricsService->collect();

        return new JsonResponse([
            'status' => $readiness->status->value,
            'liveness' => $liveness->toArray(),
            'readiness' => $readiness->toArray(),
            'system' => $systemMetrics,
            'app' => [
                'name' => config('app.name'),
                'environment' => app()->environment(),
                'debug' => config('app.debug'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
        ]);
    }
}
