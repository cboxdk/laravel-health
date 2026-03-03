<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Http\Controllers;

use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Cbox\LaravelHealth\Services\SystemMetricsService;
use Illuminate\View\View;

final class DashboardController
{
    public function __invoke(HealthCheckRunner $runner, SystemMetricsService $metricsService): View
    {
        $liveness = $runner->run(EndpointType::Liveness);
        $readiness = $runner->run(EndpointType::Readiness);

        /** @var \Illuminate\View\View */
        return view('health::dashboard', [
            'liveness' => $liveness,
            'readiness' => $readiness,
            'systemMetrics' => $metricsService->collect(),
            'prefix' => config('health.endpoints.prefix', 'health'),
            'hostname' => gethostname() ?: null,
        ]);
    }
}
