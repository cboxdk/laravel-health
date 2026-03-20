<?php

declare(strict_types=1);

use Cbox\LaravelHealth\LaravelHealth;
use Cbox\LaravelHealth\Services\SystemMetricsService;

beforeEach(function (): void {
    config()->set('health.security.public_endpoints', ['liveness']);
    config()->set('health.security.token', null);
    config()->set('health.security.allowed_ips', null);
    config()->set('health.cache.enabled', false);
    LaravelHealth::$authUsing = fn () => true;
});

afterEach(function (): void {
    LaravelHealth::$authUsing = null;
});

it('returns json metrics', function (): void {
    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('collect')->andReturn([
        'load' => ['load_1m' => 1.5, 'load_5m' => 1.2, 'load_15m' => 1.0],
        'memory' => ['total_bytes' => 16_000_000_000, 'used_bytes' => 8_000_000_000],
    ]);

    $response = $this->getJson('/health/metrics/json');

    $response->assertOk()
        ->assertJsonStructure(['load', 'memory', 'hostname']);
});

it('includes hostname in response', function (): void {
    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('collect')->andReturn([]);

    $response = $this->getJson('/health/metrics/json');

    $response->assertOk()
        ->assertJsonStructure(['hostname']);
});

it('returns empty metrics when collection fails', function (): void {
    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('collect')->andReturn([]);

    $response = $this->getJson('/health/metrics/json');

    $response->assertOk()
        ->assertJsonPath('hostname', gethostname() ?: null);
});

it('requires authentication when not public', function (): void {
    LaravelHealth::$authUsing = null;
    config()->set('health.security.token', 'secret');
    config()->set('app.env', 'production');

    $response = $this->getJson('/health/metrics/json');

    $response->assertForbidden();
});
