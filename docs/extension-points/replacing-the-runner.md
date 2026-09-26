---
title: Replacing the Runner
description: Decorate or replace the health check runner by rebinding the RunsHealthChecks contract.
weight: 62
---

# Replacing the Runner

Every endpoint, the dashboard and the `health:check` command get their reports from one contract:

```php
namespace Cbox\LaravelHealth\Contracts;

use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\LaravelHealth\Enums\EndpointType;

interface RunsHealthChecks
{
    public function run(EndpointType $type): HealthReport;
}
```

The package binds it as a singleton to `Cbox\LaravelHealth\Services\HealthCheckRunner`, which reads `health.checks.<endpoint>`, runs the checks and caches the report (see [Architecture](../core-concepts/architecture.md)). Rebind the contract in your application to wrap or replace that behaviour.

## Decorating the Default Runner

`HealthCheckRunner` is `final`, so wrap it rather than extending it. This decorator logs failing reports and how long the run took:

```php
<?php

namespace App\Health;

use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\LaravelHealth\Enums\EndpointType;
use Psr\Log\LoggerInterface;

final class LoggingHealthCheckRunner implements RunsHealthChecks
{
    public function __construct(
        private readonly RunsHealthChecks $inner,
        private readonly LoggerInterface $logger,
    ) {}

    public function run(EndpointType $type): HealthReport
    {
        $start = hrtime(true);

        $report = $this->inner->run($type);

        if (! $report->isPassing()) {
            $failing = array_filter(
                $report->results,
                fn (CheckResult $result): bool => ! $result->status->isHealthy(),
            );

            $this->logger->warning('Health checks failing', [
                'endpoint' => $type->value,
                'status' => $report->status->value,
                'checks' => array_map(fn (CheckResult $result): string => $result->name, array_values($failing)),
                'elapsed_ms' => round((hrtime(true) - $start) / 1e6, 2),
            ]);
        }

        return $report;
    }
}
```

Register it in a service provider:

```php
use App\Health\LoggingHealthCheckRunner;
use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Illuminate\Contracts\Foundation\Application;
use Psr\Log\LoggerInterface;

public function register(): void
{
    $this->app->singleton(
        RunsHealthChecks::class,
        fn (Application $app): RunsHealthChecks => new LoggingHealthCheckRunner(
            $app->make(HealthCheckRunner::class),
            $app->make(LoggerInterface::class),
        ),
    );
}
```

Your provider's binding replaces the package's. Because the decorator delegates to `HealthCheckRunner`, check resolution and [report caching](../core-concepts/caching.md) keep working. Note that `elapsed_ms` is near zero when the report comes from the cache.

`$this->app->extend(RunsHealthChecks::class, fn (RunsHealthChecks $runner, Application $app) => new LoggingHealthCheckRunner($runner, $app->make(LoggerInterface::class)))` does the same without naming `HealthCheckRunner`, and stacks with other decorators.

## Replacing the Runner

A full replacement implements `run()` for every `EndpointType` (`Liveness`, `Readiness`, `Startup`, `Status`) and returns a `HealthReport`. Build it with `HealthReport::fromResults()`, which applies the same aggregation rule as the default runner — the worst status wins, and an empty result list is `ok`:

```php
use Cbox\LaravelHealth\DataTransferObjects\HealthReport;

HealthReport::fromResults(
    type: $type,
    results: $results,          // CheckResult[]
    totalDurationMs: $elapsedMs, // optional, default 0.0
    checkedAt: null,             // optional DateTimeImmutable, default now
);
```

A replacement owns everything the default runner did: reading `health.checks.*`, turning exceptions into results, and caching.

## Testing

`fakeHealth()` from the `InteractsWithHealth` trait rebinds the same contract to `FakeHealthCheckRunner`, so a test that calls it bypasses your decorator. To test the decorator itself, construct it around a `FakeHealthCheckRunner`:

```php
use App\Health\LoggingHealthCheckRunner;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Testing\FakeHealthCheckRunner;
use Psr\Log\NullLogger;

it('delegates to the inner runner', function (): void {
    $inner = (new FakeHealthCheckRunner)
        ->returns(EndpointType::Readiness, CheckResult::critical('database'));

    $report = (new LoggingHealthCheckRunner($inner, new NullLogger))->run(EndpointType::Readiness);

    expect($report->isPassing())->toBeFalse();
    $inner->assertRan(EndpointType::Readiness, 1);
});
```

See [Testing](../getting-started/testing.md) for the full fake API.

## Related Documentation

- [Architecture](../core-concepts/architecture.md)
- [Custom Checks](custom-checks.md)
- [Testing](../getting-started/testing.md)
