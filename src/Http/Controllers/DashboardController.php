<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Http\Controllers;

use Cbox\LaravelHealth\Config\TypedConfig;
use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Services\SystemMetricsService;
use Illuminate\View\View;

final class DashboardController
{
    public function __invoke(RunsHealthChecks $runner, SystemMetricsService $metricsService): View
    {
        $liveness = $runner->run(EndpointType::Liveness);
        $readiness = $runner->run(EndpointType::Readiness);

        return view('health::dashboard', [
            'liveness' => $liveness,
            'readiness' => $readiness,
            'systemMetrics' => $metricsService->collect(),
            'prefix' => TypedConfig::string('health.endpoints.prefix', 'health'),
            'hostname' => gethostname() ?: null,
        ]);
    }
}
