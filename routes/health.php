<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Config\TypedConfig;
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

if (! TypedConfig::boolean('health.enabled', true)) {
    return;
}

/**
 * Endpoint key => [controller, default path, enabled by default].
 *
 * The key is also the config key under `health.endpoints`, the route name
 * suffix, and the endpoint name passed to EndpointAuth.
 */
$endpoints = [
    'liveness' => [LivenessController::class, '/', true],             // K8s liveness probe
    'readiness' => [ReadinessController::class, '/ready', true],      // K8s readiness probe
    'startup' => [StartupController::class, '/startup', true],        // K8s startup probe
    'status' => [StatusController::class, '/status', true],           // Full status
    'metrics' => [PrometheusController::class, '/metrics', true],     // Prometheus metrics
    'json' => [JsonMetricsController::class, '/metrics/json', true],  // JSON metrics
    'ui' => [DashboardController::class, '/ui', false],               // HTML dashboard
];

Route::prefix(TypedConfig::string('health.endpoints.prefix', 'health'))
    ->middleware([
        ...TypedConfig::stringList('health.middleware', ['api']),
        AllowIps::class,
    ])
    ->group(function () use ($endpoints): void {
        foreach ($endpoints as $endpoint => [$controller, $defaultPath, $enabledByDefault]) {
            if (! TypedConfig::boolean("health.endpoints.{$endpoint}.enabled", $enabledByDefault)) {
                continue;
            }

            // HEALTH_PROMETHEUS_ENABLED switches the Prometheus scrape endpoint off without touching the endpoint map.
            if ($endpoint === 'metrics' && ! TypedConfig::boolean('health.metrics.prometheus.enabled', true)) {
                continue;
            }

            Route::get(TypedConfig::string("health.endpoints.{$endpoint}.path", $defaultPath), $controller)
                ->middleware(EndpointAuth::class.':'.$endpoint)
                ->name("health.{$endpoint}");
        }
    });
