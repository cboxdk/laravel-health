<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Http\Controllers\DashboardController;
use Cbox\LaravelHealth\Http\Middleware\AllowIps;
use Cbox\LaravelHealth\Http\Middleware\EndpointAuth;
use Cbox\LaravelHealth\LaravelHealth;
use Cbox\LaravelHealth\Services\SystemMetricsService;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    config()->set('health.security.public_endpoints', ['liveness']);
    config()->set('health.security.token', null);
    config()->set('health.security.allowed_ips', null);
    config()->set('health.cache.enabled', false);
    config()->set('health.checks.liveness', []);
    config()->set('health.checks.readiness', []);
    LaravelHealth::$authUsing = fn () => true;

    // The UI route is disabled by default, so we register it manually for testing
    Route::prefix('health')
        ->middleware([AllowIps::class])
        ->group(function (): void {
            Route::get('/ui', DashboardController::class)
                ->middleware(EndpointAuth::class.':ui')
                ->name('health.ui.test');
        });
});

afterEach(function (): void {
    LaravelHealth::$authUsing = null;
});

it('returns html dashboard', function (): void {
    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('collect')->andReturn([]);

    $response = $this->get('/health/ui');

    $response->assertOk()
        ->assertViewIs('health::dashboard');
});

it('passes health data to view', function (): void {
    $metricsService = mock(SystemMetricsService::class);
    $metricsService->shouldReceive('collect')->andReturn(['load' => ['load_1m' => 1.0]]);

    $response = $this->get('/health/ui');

    $response->assertOk()
        ->assertViewHasAll(['liveness', 'readiness', 'systemMetrics', 'prefix', 'hostname']);
});

it('is disabled by default in config', function (): void {
    expect(config('health.endpoints.ui.enabled'))->toBeFalse();
});
