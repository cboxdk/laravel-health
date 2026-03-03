<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Config\HealthConfig;
use Cbox\LaravelHealth\LaravelHealth;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Cbox\LaravelHealth\Services\PrometheusRenderer;
use Cbox\LaravelHealth\Services\SystemMetricsService;

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
