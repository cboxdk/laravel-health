---
title: Testing
description: Fake the health check runner and system metrics when testing code that uses Health for Laravel.
weight: 12
---

# Testing

There are two layers you can fake:

- **The health check runner** — `InteractsWithHealth` swaps the `RunsHealthChecks` binding for an in-memory `FakeHealthCheckRunner`. Use it when you test what your application does with a health result (routes, middleware, monitoring glue) and don't want real database, cache or queue checks to run.
- **System metrics** — `cboxdk/system-metrics` ships `FakeSystemMetrics`, which replaces every metrics source with predictable data. Use it when you want the real checks and renderers to run against known CPU, memory and disk values.

## Setup

Add the trait to your base test case:

```php
namespace Tests;

use Cbox\LaravelHealth\Testing\InteractsWithHealth;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithHealth;
}
```

It gives you three methods:

| Method | What it does |
|--------|--------------|
| `fakeHealth(): FakeHealthCheckRunner` | Binds a new `FakeHealthCheckRunner` as `RunsHealthChecks` and returns it |
| `authorizeHealthEndpoints(bool $allowed = true)` | Sets `LaravelHealth::auth(fn () => $allowed)`, so requests pass (or fail) the auth callback |
| `resetHealthAuthorization()` | Clears the auth callback |

### Authorizing requests

Outside the `local` environment, every endpoint except liveness returns `403` unless the request carries the configured token or the auth callback allows it (see [Endpoint Security](../security/endpoint-security.md)). Tests run in the `testing` environment, so call `authorizeHealthEndpoints()` before hitting protected endpoints. Token and IP allowlist rules still apply — clear them if your test config sets them.

The auth callback is stored in a static property and survives between tests. Always reset it:

```php
beforeEach(function (): void {
    config()->set('health.security.token', null);
    config()->set('health.security.allowed_ips', null);
});

afterEach(function (): void {
    $this->resetHealthAuthorization();
});
```

## Faking the Runner

`fakeHealth()` returns a `FakeHealthCheckRunner`. Every endpoint, the dashboard and the `health:check` command resolve `RunsHealthChecks`, so they all use the fake.

| Method | Description |
|--------|-------------|
| `returns(EndpointType $type, CheckResult ...$results)` | Make `run($type)` return a report built from these results (worst status wins) |
| `returnsReport(HealthReport $report)` | Return a report you built yourself, keyed by `$report->type` |
| `run(EndpointType $type)` | Record the call and return the prepared report |
| `runs()` | Every endpoint that was run, in order |
| `assertRan(EndpointType $type, ?int $times = null)` | Assert the endpoint ran at least once, or exactly `$times` times |
| `assertNotRan(EndpointType $type)` | Assert the endpoint never ran |

An endpoint you didn't prepare returns a healthy, empty report (status `ok`, no checks).

```php
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\Enums\EndpointType;

it('returns 503 when readiness fails', function (): void {
    $this->authorizeHealthEndpoints();

    $fake = $this->fakeHealth()
        ->returns(EndpointType::Readiness, CheckResult::critical('database', 'Connection refused'));

    $this->getJson('/health')->assertOk()->assertJsonPath('status', 'ok');

    $this->getJson('/health/ready')
        ->assertServiceUnavailable()
        ->assertJsonPath('status', 'critical')
        ->assertJsonPath('checks.database.message', 'Connection refused');

    $fake->assertRan(EndpointType::Liveness, 1);
    $fake->assertRan(EndpointType::Readiness, 1);
    $fake->assertNotRan(EndpointType::Startup);
});

it('treats a warning as passing', function (): void {
    $this->authorizeHealthEndpoints();

    $this->fakeHealth()->returns(EndpointType::Startup, CheckResult::warning('schedule', 'No heartbeat yet'));

    $this->getJson('/health/startup')->assertOk()->assertJsonPath('status', 'warning');
});
```

### The `health:check` command

The command uses the same contract, so the fake drives its exit code:

```php
it('fails the command when readiness is critical', function (): void {
    $this->fakeHealth()->returns(EndpointType::Readiness, CheckResult::critical('queue'));

    $this->artisan('health:check', ['--endpoint' => 'liveness'])->assertSuccessful();
    $this->artisan('health:check', ['--endpoint' => 'readiness'])->assertFailed();
});
```

### Denying access

```php
it('denies access when the callback refuses', function (): void {
    $this->authorizeHealthEndpoints(false);
    $this->fakeHealth();

    $this->getJson('/health/ready')->assertForbidden();
});
```

### Building a report yourself

`returnsReport()` takes any `HealthReport`. `HealthReport::fromResults()` aggregates results the same way the real runner does — the worst status wins, and no results means `ok`:

```php
use Cbox\LaravelHealth\DataTransferObjects\HealthReport;

$this->fakeHealth()->returnsReport(HealthReport::fromResults(
    EndpointType::Readiness,
    [CheckResult::ok('database'), CheckResult::warning('queue', 'Backlog growing')],
    totalDurationMs: 12.5,
));
```

## Faking System Metrics

The `cboxdk/system-metrics` package ships with built-in fakes. They let you test the CPU, memory and disk checks, Prometheus output and dashboard rendering without real system calls.

```php
use Cbox\SystemMetrics\Testing\FakeSystemMetrics;

beforeEach(function () {
    FakeSystemMetrics::install();
});

afterEach(function () {
    FakeSystemMetrics::uninstall();
});
```

`FakeSystemMetrics::install()` replaces every system metrics source with a fake that returns predictable data. `uninstall()` restores the real implementations. The fakes are static, so always uninstall them.

The examples below hit protected endpoints, so they also call `authorizeHealthEndpoints()` (see [Authorizing requests](#authorizing-requests)).

### Report caching in tests

The runner caches each endpoint's report for `health.cache.ttl` seconds (see [Caching](../core-concepts/caching.md)). If a single test calls the same endpoint twice with different fake data, the second call gets the cached report. Disable caching for those tests:

```php
config()->set('health.cache.enabled', false);
```

### Customizing Fake Data

`install()` returns an object with a public property for each fake source:

```php
$fakes = FakeSystemMetrics::install();

$fakes->cpu;          // FakeCpuMetricsSource
$fakes->memory;       // FakeMemoryMetricsSource
$fakes->loadAverage;  // FakeLoadAverageSource
$fakes->storage;      // FakeStorageMetricsSource
$fakes->network;      // FakeNetworkMetricsSource
$fakes->uptime;       // FakeUptimeSource
$fakes->limits;       // FakeSystemLimitsSource
$fakes->container;    // FakeContainerMetricsSource
$fakes->environment;  // FakeEnvironmentDetector
```

Each fake has a `set()` method that accepts the corresponding DTO:

```php
use Cbox\SystemMetrics\DTO\Metrics\LoadAverageSnapshot;
use Cbox\SystemMetrics\DTO\Metrics\Memory\MemorySnapshot;

$fakes = FakeSystemMetrics::install();

// High load
$fakes->loadAverage->set(new LoadAverageSnapshot(
    oneMinute: 12.0,
    fiveMinutes: 10.0,
    fifteenMinutes: 8.0,
));

// Low memory
$fakes->memory->set(new MemorySnapshot(
    totalBytes: 8_589_934_592,
    freeBytes: 200_000_000,
    availableBytes: 400_000_000,
    usedBytes: 8_189_934_592,
    buffersBytes: 100_000_000,
    cachedBytes: 100_000_000,
    swapTotalBytes: 2_147_483_648,
    swapFreeBytes: 0,
    swapUsedBytes: 2_147_483_648,
));
```

### Simulating Failures

Every fake has a `failWith()` method that makes the source return a `Result::failure()`:

```php
use Cbox\SystemMetrics\Exceptions\SystemMetricsException;

$fakes = FakeSystemMetrics::install();

$fakes->memory->failWith(
    new SystemMetricsException('Memory read failed')
);

// SystemMetrics::memory() now returns a failure Result.
// MemoryCheck reports `unknown` ("Unable to read memory metrics").
```

The CPU, memory and disk space checks all report `unknown` when their metrics can't be read, which makes a probe return `503`.

### Simulating Containers

By default, the container fake returns a failure (simulating a bare-metal or VM environment). Use `asContainer()` to simulate a cgroup-limited container:

```php
$fakes = FakeSystemMetrics::install();

$fakes->container->asContainer(
    cpuQuota: 2.0,                       // 2 CPU cores allocated
    memoryLimitBytes: 4_294_967_296,     // 4 GB memory limit
);

// SystemMetrics::container() now returns ContainerLimits
// SystemMetrics::overview()->container is no longer null
```

### Defaults

When no custom data is provided, the fakes return sensible defaults representing a healthy Linux server:

| Source | Default |
|---|---|
| Environment | Ubuntu 22.04, x86_64, bare metal |
| CPU | 4 cores, moderate usage |
| Memory | 8 GB total, 50% used |
| Load average | 0.5 / 0.3 / 0.2 |
| Storage | Single `/` mount, 100 GB, 50% used |
| Network | Single `eth0` interface, up |
| Uptime | 1 day |
| System limits | 4 cores, 8 GB, host source |
| Container | Not containerized (failure result) |

### Testing Health Checks

The system checks only run on endpoints you assign them to. The default readiness checks don't include them, so set the checks for the test:

```php
use Cbox\LaravelHealth\Checks\CpuCheck;
use Cbox\LaravelHealth\Checks\MemoryCheck;
use Cbox\SystemMetrics\DTO\Metrics\Memory\MemorySnapshot;
use Cbox\SystemMetrics\Testing\FakeSystemMetrics;

beforeEach(function (): void {
    $this->authorizeHealthEndpoints();
});

afterEach(function (): void {
    $this->resetHealthAuthorization();
    FakeSystemMetrics::uninstall();
});

it('passes readiness when CPU load is low', function (): void {
    FakeSystemMetrics::install();
    config()->set('health.checks.readiness', [CpuCheck::class]);

    $this->getJson('/health/ready')->assertOk();
});

it('fails readiness when memory exceeds threshold', function (): void {
    $fakes = FakeSystemMetrics::install();
    config()->set('health.checks.readiness', [MemoryCheck::class]);

    $fakes->memory->set(new MemorySnapshot(
        totalBytes: 8_589_934_592,
        freeBytes: 400_000_000,
        availableBytes: 600_000_000,
        usedBytes: 7_989_934_592,       // ~93% used
        buffersBytes: 100_000_000,
        cachedBytes: 100_000_000,
        swapTotalBytes: 0,
        swapFreeBytes: 0,
        swapUsedBytes: 0,
    ));

    $this->getJson('/health/ready')->assertStatus(503);
});
```

### Testing Prometheus Output

Metric names carry the configured namespace (`app` by default):

```php
use Cbox\SystemMetrics\Testing\FakeSystemMetrics;

it('exposes memory metrics in prometheus format', function () {
    FakeSystemMetrics::install();

    $response = $this->get('/health/metrics');

    $response->assertOk();
    $response->assertSee('app_system_memory_total_bytes 8589934592');
    $response->assertSee('app_system_memory_used_bytes 4294967296');
});
```

### Testing JSON Metrics

```php
use Cbox\SystemMetrics\Testing\FakeSystemMetrics;

it('returns system metrics as json', function () {
    FakeSystemMetrics::install();

    $response = $this->getJson('/health/metrics/json');

    $response->assertOk();
    $response->assertJsonPath('memory.total_bytes', 8589934592);
    $response->assertJsonPath('load.load_1m', 0.5);
});
```

### Using Without Laravel

The system-metrics fakes work with any PHP application — they operate on the static `SystemMetricsConfig` directly:

```php
use Cbox\SystemMetrics\SystemMetrics;
use Cbox\SystemMetrics\Testing\FakeSystemMetrics;

FakeSystemMetrics::install();

$result = SystemMetrics::overview();
$overview = $result->getValue();

echo $overview->memory->totalBytes; // 8589934592

FakeSystemMetrics::uninstall();
```

## Related Documentation

- [Replacing the Runner](../extension-points/replacing-the-runner.md)
- [System Metrics](../core-concepts/system-metrics.md)
- [Custom Checks](../extension-points/custom-checks.md)
- [CPU Check](../health-checks/cpu.md)
- [Memory Check](../health-checks/memory.md)
- [Disk Space Check](../health-checks/disk-space.md)
