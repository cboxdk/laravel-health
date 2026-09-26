<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Testing;

use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\LaravelHealth;
use Illuminate\Http\Request;

/**
 * Test helpers for applications (and this package) that exercise health endpoints.
 *
 * Use it in a Laravel / Testbench test case.
 */
trait InteractsWithHealth
{
    /**
     * Replace the health check runner with an in-memory fake and return it.
     */
    protected function fakeHealth(): FakeHealthCheckRunner
    {
        $fake = new FakeHealthCheckRunner;

        app()->instance(RunsHealthChecks::class, $fake);

        return $fake;
    }

    /**
     * Let every request through the endpoint auth callback (token and IP rules still apply).
     */
    protected function authorizeHealthEndpoints(bool $allowed = true): void
    {
        LaravelHealth::auth(fn (Request $request): bool => $allowed);
    }

    /**
     * Restore the default auth callback. Call it in tearDown/afterEach: the callback is
     * static and would otherwise leak into the next test.
     */
    protected function resetHealthAuthorization(): void
    {
        LaravelHealth::$authUsing = null;
    }
}
