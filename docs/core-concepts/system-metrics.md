---
title: System Metrics
description: Integration with cboxdk/system-metrics and container awareness.
weight: 33
---

# System Metrics

Health for Laravel uses [cboxdk/system-metrics](https://github.com/cboxdk/system-metrics) to collect CPU, memory, disk, network, and uptime metrics across Linux, macOS, and containerized environments.

## How It Works

`SystemMetricsService` calls `SystemMetrics::overview()` to collect a snapshot of system state. This data powers:

- [Prometheus metrics](../endpoints/prometheus-metrics.md) at `/health/metrics`
- [JSON metrics](../endpoints/json-metrics.md) at `/health/metrics/json`
- The `system` section of `/health/status`
- [HTML dashboard](../endpoints/dashboard.md) at `/health/ui`

The system health checks ([CPU](../health-checks/cpu.md), [Memory](../health-checks/memory.md), [Disk Space](../health-checks/disk-space.md)) call `cboxdk/system-metrics` directly rather than going through this snapshot.

If the overview can't be read, the metrics endpoints return no system metrics instead of failing.

## Container Awareness

When running inside Docker or Kubernetes, `system-metrics` detects cgroup limits (v1 and v2). The metrics output then reports:

- **Memory**: container limit and usage instead of host memory
- **CPU**: quota and throttle information
- **OOM kills**: out-of-memory kill count

The `memory.source` field in JSON metrics indicates the source: `host`, `cgroup_v1`, or `cgroup_v2`.

The [Memory check](../health-checks/memory.md) uses the same limits, so inside a container it compares usage against the container's memory limit. The [CPU check](../health-checks/cpu.md) uses the load average, which the kernel reports for the whole host; use the container CPU metrics in Prometheus to alert on a container's CPU quota.

## Toggling Metric Groups

Enable or disable metric groups in `config/health.php`:

```php
'metrics' => [
    'system' => [
        'memory'  => true,
        'load'    => true,
        'storage' => true,
        'network' => true,
    ],
],
```

Disabled groups are omitted from the Prometheus, JSON, status and dashboard output. The environment, uptime and container sections have no toggle. The toggles don't affect the health checks.

## Related Documentation

- [Prometheus Metrics](../endpoints/prometheus-metrics.md)
- [JSON Metrics](../endpoints/json-metrics.md)
- [CPU Check](../health-checks/cpu.md)
- [Memory Check](../health-checks/memory.md)
- [Disk Space Check](../health-checks/disk-space.md)
- [Testing](../getting-started/testing.md) — fake system metrics in tests
