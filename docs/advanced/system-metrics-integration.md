---
title: System Metrics Integration
description: Integration with cboxdk/system-metrics and container awareness.
weight: 44
---

# System Metrics Integration

Health for Laravel uses [cboxdk/system-metrics](https://github.com/cboxdk/system-metrics) to collect CPU, memory, disk, network, and uptime metrics across Linux, macOS, and containerized environments.

## How It Works

The `SystemMetricsService` calls `SystemMetrics::overview()` to collect a snapshot of system state. This data powers:

- [Prometheus metrics](../endpoints/prometheus-metrics.md) at `/health/metrics`
- [JSON metrics](../endpoints/json-metrics.md) at `/health/metrics/json`
- [HTML dashboard](../endpoints/dashboard.md) at `/health/ui`
- System health checks ([CPU](../health-checks/cpu.md), [Memory](../health-checks/memory.md), [Disk Space](../health-checks/disk-space.md))

## Container Awareness

When running inside Docker or Kubernetes, `system-metrics` automatically detects cgroup limits (v1 and v2) and reports:

- **Memory**: container limit and usage instead of host memory
- **CPU**: quota and throttle information
- **OOM kills**: out-of-memory kill count

The `memory.source` field in JSON metrics indicates the source: `host`, `cgroup_v1`, or `cgroup_v2`.

## Toggling Metric Groups

Enable or disable individual metric groups in `config/health.php`:

```php
'metrics' => [
    'system' => [
        'cpu'     => true,
        'memory'  => true,
        'load'    => true,
        'storage' => true,
        'network' => true,
    ],
],
```

Disabled groups are omitted from both Prometheus and JSON output.

## Related Documentation

- [Prometheus Metrics](../endpoints/prometheus-metrics.md)
- [JSON Metrics](../endpoints/json-metrics.md)
- [CPU Check](../health-checks/cpu.md)
- [Memory Check](../health-checks/memory.md)
- [Disk Space Check](../health-checks/disk-space.md)
