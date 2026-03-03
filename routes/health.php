<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Http\Controllers\DashboardController;
use Cbox\LaravelHealth\Http\Controllers\JsonMetricsController;
use Cbox\LaravelHealth\Http\Controllers\LivenessController;
use Cbox\LaravelHealth\Http\Controllers\PrometheusController;
use Cbox\LaravelHealth\Http\Controllers\ReadinessController;
use Cbox\LaravelHealth\Http\Controllers\StartupController;
use Cbox\LaravelHealth\Http\Controllers\StatusController;
use Cbox\LaravelHealth\Http\Middleware\AllowIps;
use Cbox\LaravelHealth\Http\Middleware\EndpointAuth;
use Illuminate\Support\Facades\Route;

if (! config('health.enabled', true)) {
    return;
}

Route::prefix(config('health.endpoints.prefix', 'health'))
    ->middleware(array_merge(
        (array) config('health.middleware', ['api']),
        [AllowIps::class],
    ))
    ->group(function (): void {
        // Liveness probe (K8s)
        if (config('health.endpoints.liveness.enabled', true)) {
            Route::get(
                config('health.endpoints.liveness.path', '/'),
                LivenessController::class,
            )
                ->middleware(EndpointAuth::class.':liveness')
                ->name('health.liveness');
        }

        // Readiness probe (K8s)
        if (config('health.endpoints.readiness.enabled', true)) {
            Route::get(
                config('health.endpoints.readiness.path', '/ready'),
                ReadinessController::class,
            )
                ->middleware(EndpointAuth::class.':readiness')
                ->name('health.readiness');
        }

        // Startup probe (K8s)
        if (config('health.endpoints.startup.enabled', true)) {
            Route::get(
                config('health.endpoints.startup.path', '/startup'),
                StartupController::class,
            )
                ->middleware(EndpointAuth::class.':startup')
                ->name('health.startup');
        }

        // Full status
        if (config('health.endpoints.status.enabled', true)) {
            Route::get(
                config('health.endpoints.status.path', '/status'),
                StatusController::class,
            )
                ->middleware(EndpointAuth::class.':status')
                ->name('health.status');
        }

        // Prometheus metrics
        if (config('health.endpoints.metrics.enabled', true)) {
            Route::get(
                config('health.endpoints.metrics.path', '/metrics'),
                PrometheusController::class,
            )
                ->middleware(EndpointAuth::class.':metrics')
                ->name('health.metrics');
        }

        // JSON metrics
        if (config('health.endpoints.json.enabled', true)) {
            Route::get(
                config('health.endpoints.json.path', '/metrics/json'),
                JsonMetricsController::class,
            )
                ->middleware(EndpointAuth::class.':json')
                ->name('health.json');
        }

        // HTML Dashboard
        if (config('health.endpoints.ui.enabled', false)) {
            Route::get(
                config('health.endpoints.ui.path', '/ui'),
                DashboardController::class,
            )
                ->middleware(EndpointAuth::class.':ui')
                ->name('health.ui');
        }
    });
