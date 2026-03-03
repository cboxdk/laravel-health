<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Http\Controllers;

use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Cbox\LaravelHealth\Services\PrometheusRenderer;
use Cbox\LaravelHealth\Services\SystemMetricsService;
use Illuminate\Http\Response;

final class PrometheusController
{
    public function __invoke(
        HealthCheckRunner $runner,
        PrometheusRenderer $renderer,
        SystemMetricsService $metricsService,
    ): Response {
        $liveness = $runner->run(EndpointType::Liveness);
        $readiness = $runner->run(EndpointType::Readiness);

        $output = $renderer->render(
            livenessReport: $liveness,
            readinessReport: $readiness,
            metricsService: $metricsService,
        );

        return new Response($output, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }
}
