<?php

declare(strict_types=1);

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Enums\Status;
use Cbox\LaravelHealth\Services\PrometheusRenderer;
use Cbox\LaravelHealth\Services\SystemMetricsService;

it('renders health check metrics in prometheus format', function (): void {
    $renderer = new PrometheusRenderer('test_app');

    $liveness = new HealthReport(
        type: EndpointType::Liveness,
        status: Status::Ok,
        results: [
            CheckResult::ok('database', 'OK')->withDuration(5.0),
        ],
        totalDurationMs: 5.0,
        checkedAt: new DateTimeImmutable,
    );

    $readiness = new HealthReport(
        type: EndpointType::Readiness,
        status: Status::Ok,
        results: [
            CheckResult::ok('database', 'OK')->withDuration(5.0),
            CheckResult::ok('cache', 'OK')->withDuration(1.0),
        ],
        totalDurationMs: 6.0,
        checkedAt: new DateTimeImmutable,
    );

    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('getOverview')->andReturn(null);

    $output = $renderer->render($liveness, $readiness, $metricsService);

    expect($output)->toContain('# HELP test_app_health_check_status')
        ->and($output)->toContain('# TYPE test_app_health_check_status gauge')
        ->and($output)->toContain('test_app_health_check_status{check="database"} 1.0')
        ->and($output)->toContain('test_app_health_check_status{check="cache"} 1.0')
        ->and($output)->toContain('test_app_health_check_duration_seconds{check="database"}')
        ->and($output)->toContain('test_app_health_check_duration_seconds{check="cache"}');
});

it('renders correct status values', function (): void {
    $renderer = new PrometheusRenderer('app');

    $liveness = new HealthReport(
        type: EndpointType::Liveness,
        status: Status::Ok,
        results: [],
        totalDurationMs: 0.0,
        checkedAt: new DateTimeImmutable,
    );

    $readiness = new HealthReport(
        type: EndpointType::Readiness,
        status: Status::Critical,
        results: [
            CheckResult::ok('database', 'OK')->withDuration(1.0),
            CheckResult::warning('cache', 'Slow')->withDuration(2.0),
            CheckResult::critical('queue', 'Down')->withDuration(3.0),
        ],
        totalDurationMs: 6.0,
        checkedAt: new DateTimeImmutable,
    );

    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('getOverview')->andReturn(null);

    $output = $renderer->render($liveness, $readiness, $metricsService);

    expect($output)->toContain('app_health_check_status{check="database"} 1.0')
        ->and($output)->toContain('app_health_check_status{check="cache"} 0.5')
        ->and($output)->toContain('app_health_check_status{check="queue"} 0.0');
});
