---
title: Testing
description: Testing system metrics health checks with fake data.
weight: 45
---

# Testing

The `cboxdk/system-metrics` package ships with built-in fakes for testing. This lets you test health checks, Prometheus output, and dashboard rendering without real system calls.

## Quick Start

```php
use Cbox\SystemMetrics\Testing\FakeSystemMetrics;

beforeEach(function () {
    FakeSystemMetrics::install();
});

afterEach(function () {
    FakeSystemMetrics::uninstall();
});

it('reports healthy when CPU load is low', function () {
    $response = $this->get('/health/ready');

    $response->assertOk();
});
```

`FakeSystemMetrics::install()` replaces every system metrics source with a fake that returns predictable data. `uninstall()` restores the real implementations.

## Customizing Fake Data

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

## Simulating Failures

Every fake has a `failWith()` method that makes the source return a `Result::failure()`:

```php
use Cbox\SystemMetrics\Exceptions\SystemMetricsException;

$fakes = FakeSystemMetrics::install();

$fakes->memory->failWith(
    new SystemMetricsException('Memory read failed')
);

// SystemMetrics::memory() now returns a failure Result
// Health checks that depend on memory will report degraded/unhealthy
```

Failure simulation is useful for testing how your application handles metric collection errors.

## Simulating Containers

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

## Defaults

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

## Testing Health Checks

Example testing a memory check threshold:

```php
use Cbox\SystemMetrics\DTO\Metrics\Memory\MemorySnapshot;
use Cbox\SystemMetrics\Testing\FakeSystemMetrics;

it('fails readiness when memory exceeds threshold', function () {
    $fakes = FakeSystemMetrics::install();

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

    $response = $this->get('/health/ready');

    $response->assertStatus(503);
});
```

## Testing Prometheus Output

```php
use Cbox\SystemMetrics\Testing\FakeSystemMetrics;

it('exposes memory metrics in prometheus format', function () {
    FakeSystemMetrics::install();

    $response = $this->get('/health/metrics');

    $response->assertOk();
    $response->assertSee('system_memory_total_bytes 8589934592');
    $response->assertSee('system_memory_used_bytes 4294967296');
});
```

## Testing JSON Metrics

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

## Using Without Laravel

The fakes work with any PHP application — they operate on the static `SystemMetricsConfig` directly:

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

- [System Metrics Integration](system-metrics-integration.md)
- [Custom Checks](custom-checks.md)
- [CPU Check](../health-checks/cpu.md)
- [Memory Check](../health-checks/memory.md)
- [Disk Space Check](../health-checks/disk-space.md)
