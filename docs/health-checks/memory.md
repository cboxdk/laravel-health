---
title: Memory Check
description: Monitor system memory usage.
weight: 19
---

# Memory Check

Reads system memory usage and compares against a percentage threshold. Automatically uses cgroup limits when running inside containers.

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
- Uses cgroup memory limits in containers, host memory otherwise
- Returns `critical` when used percentage exceeds `memory_percent`
- Metadata: `used_percent`, `used_bytes`, `total_bytes`, `threshold`

## Related Documentation

- [Health Checks Overview](_index.md)
- [CPU Check](cpu.md)
- [System Metrics Integration](../advanced/system-metrics-integration.md)
