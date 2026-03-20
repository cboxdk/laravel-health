<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Contracts\HealthCheck;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\LaravelHealth;
use Cbox\LaravelHealth\Services\SystemMetricsService;

beforeEach(function (): void {
    config()->set('health.security.public_endpoints', ['liveness']);
    config()->set('health.security.token', null);
    config()->set('health.security.allowed_ips', null);
    config()->set('health.cache.enabled', false);
    config()->set('health.checks.liveness', []);
    config()->set('health.checks.readiness', []);
    LaravelHealth::$authUsing = fn () => true;
});

afterEach(function (): void {
    LaravelHealth::$authUsing = null;
});

it('returns full status report', function (): void {
    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('collect')->andReturn([
        'load' => ['load_1m' => 1.5, 'load_5m' => 1.2, 'load_15m' => 1.0],
    ]);

    $response = $this->getJson('/health/status');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'liveness',
            'readiness',
            'system',
            'app' => ['name', 'environment', 'debug', 'hostname', 'php_version', 'laravel_version'],
        ]);
});

it('includes liveness and readiness reports', function (): void {
    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('collect')->andReturn([]);

    $response = $this->getJson('/health/status');

    $response->assertOk()
        ->assertJsonPath('liveness.type', 'liveness')
        ->assertJsonPath('readiness.type', 'readiness');
});

it('includes app metadata', function (): void {
    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('collect')->andReturn([]);

    $response = $this->getJson('/health/status');

    $response->assertOk()
        ->assertJsonPath('app.php_version', PHP_VERSION);
});

it('reflects worst readiness status', function (): void {
    $failingCheck = new class implements HealthCheck
    {
        public function name(): string
        {
            return 'failing';
        }

        public function run(): CheckResult
        {
            return CheckResult::critical('failing', 'Down');
        }
    };

    config()->set('health.checks.readiness', [$failingCheck::class]);
    app()->bind($failingCheck::class, fn () => $failingCheck);

    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('collect')->andReturn([]);

    $response = $this->getJson('/health/status');

    $response->assertOk()
        ->assertJsonPath('status', 'critical');
});

it('requires authentication when not public', function (): void {
    LaravelHealth::$authUsing = null;
    config()->set('health.security.token', 'secret');
    config()->set('app.env', 'production');

    $response = $this->getJson('/health/status');

    $response->assertForbidden();
});
