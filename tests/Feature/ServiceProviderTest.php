<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Config\HealthConfig;
use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\LaravelHealth;
use Cbox\LaravelHealth\LaravelHealthServiceProvider;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Cbox\LaravelHealth\Services\PrometheusRenderer;
use Cbox\LaravelHealth\Services\SystemMetricsService;
use Cbox\LaravelHealth\Testing\FakeHealthCheckRunner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

it('registers HealthConfig singleton', function (): void {
    expect(app(HealthConfig::class))->toBeInstanceOf(HealthConfig::class);
});

it('registers HealthCheckRunner singleton', function (): void {
    expect(app(HealthCheckRunner::class))->toBeInstanceOf(HealthCheckRunner::class);
});

it('registers SystemMetricsService singleton', function (): void {
    expect(app(SystemMetricsService::class))->toBeInstanceOf(SystemMetricsService::class);
});

it('registers PrometheusRenderer singleton', function (): void {
    expect(app(PrometheusRenderer::class))->toBeInstanceOf(PrometheusRenderer::class);
});

it('registers LaravelHealth singleton', function (): void {
    expect(app(LaravelHealth::class))->toBeInstanceOf(LaravelHealth::class);
});

it('loads config', function (): void {
    expect(config('health.enabled'))->toBeTrue()
        ->and(config('health.endpoints.prefix'))->toBe('health')
        ->and(config('health.endpoints.liveness.enabled'))->toBeTrue();
});

it('binds the runner contract to the shared runner', function (): void {
    expect(app(RunsHealthChecks::class))->toBe(app(HealthCheckRunner::class));
});

it('lets the host replace the runner contract', function (): void {
    $custom = new FakeHealthCheckRunner;
    app()->instance(RunsHealthChecks::class, $custom);

    expect(app(RunsHealthChecks::class))->toBe($custom);
});

it('registers the artisan commands', function (): void {
    $commands = array_keys(Artisan::all());

    expect($commands)->toContain('health:check')->toContain('health:heartbeat');
});

it('registers the view namespace', function (): void {
    expect(view()->exists('health::dashboard'))->toBeTrue();
});

it('publishes config and views under the documented tags', function (): void {
    $config = ServiceProvider::pathsToPublish(LaravelHealthServiceProvider::class, 'health-config');
    $views = ServiceProvider::pathsToPublish(LaravelHealthServiceProvider::class, 'health-views');

    expect(array_values($config))->toBe([config_path('health.php')])
        ->and(array_values($views))->toBe([resource_path('views/vendor/health')])
        ->and(array_keys($config)[0])->toEndWith('config/health.php');
});
