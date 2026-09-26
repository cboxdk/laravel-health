---
title: Memory Check
description: Monitor system memory usage.
weight: 49
---

# Memory Check

Reads memory usage and compares it against a percentage threshold. Inside a cgroup-limited container it measures against the container's memory limit, not the host's RAM.

## Configuration

```php
'thresholds' => [
    'memory_percent' => 90,
],
```

## Usage

```php
use Cbox\LaravelHealth\Checks\MemoryCheck;

'checks' => [
    'readiness' => [
        MemoryCheck::class,
    ],
],
```

## Behavior

- Reads memory metrics via [cboxdk/system-metrics](https://github.com/cboxdk/system-metrics)
- Uses the system limits first: the cgroup v1/v2 memory limit inside a container, host RAM on bare metal or a VM. This matches the memory figures in the [Prometheus](../endpoints/prometheus-metrics.md) and [JSON](../endpoints/json-metrics.md) endpoints
- Falls back to memory as reported by the operating system (for example `/proc/meminfo`) when limits can't be read
- Returns `critical` when used percentage reaches or exceeds `memory_percent`
- Returns `unknown` when no memory metrics can be read
- Metadata: `used_percent`, `used_bytes`, `total_bytes`, `threshold`, `source` (`cgroup_v2`, `cgroup_v1` or `host`)

## Related Documentation

- [Health Checks Overview](_index.md)
- [CPU Check](cpu.md)
- [System Metrics](../core-concepts/system-metrics.md)
