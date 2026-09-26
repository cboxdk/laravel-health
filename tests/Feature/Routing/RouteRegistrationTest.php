<?php

declare(strict_types=1);

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;

/**
 * Routes are registered at boot, so re-run the route file after changing config.
 */
function reloadHealthRoutes(): void
{
    Route::setRoutes(new RouteCollection);

    require __DIR__.'/../../../routes/health.php';

    Route::getRoutes()->refreshNameLookups();
}

it('registers every endpoint by default except the dashboard', function (): void {
    reloadHealthRoutes();

    foreach (['liveness', 'readiness', 'startup', 'status', 'metrics', 'json'] as $endpoint) {
        expect(Route::has("health.{$endpoint}"))->toBeTrue("health.{$endpoint} is missing");
    }

    expect(Route::has('health.ui'))->toBeFalse();
});

it('uses the configured prefix and paths', function (): void {
    config()->set('health.endpoints.prefix', 'ops');
    config()->set('health.endpoints.readiness.path', '/readyz');

    reloadHealthRoutes();

    expect(route('health.readiness', absolute: false))->toBe('/ops/readyz')
        ->and(route('health.liveness', absolute: false))->toBe('/ops');
});

it('skips disabled endpoints', function (): void {
    config()->set('health.endpoints.status.enabled', false);
    config()->set('health.endpoints.ui.enabled', true);

    reloadHealthRoutes();

    expect(Route::has('health.status'))->toBeFalse()
        ->and(Route::has('health.ui'))->toBeTrue();
});

it('disables the Prometheus endpoint via metrics.prometheus.enabled', function (): void {
    config()->set('health.metrics.prometheus.enabled', false);

    reloadHealthRoutes();

    expect(Route::has('health.metrics'))->toBeFalse()
        ->and(Route::has('health.json'))->toBeTrue();
});

it('registers no routes when the package is disabled', function (): void {
    config()->set('health.enabled', false);

    reloadHealthRoutes();

    expect(Route::getRoutes()->count())->toBe(0);
});

it('applies the configured middleware stack', function (): void {
    config()->set('health.middleware', 'web');

    reloadHealthRoutes();

    expect(Route::getRoutes()->getByName('health.liveness')?->middleware())
        ->toContain('web')
        ->toContain('Cbox\LaravelHealth\Http\Middleware\AllowIps');
});
