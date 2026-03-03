---
title: CPU Check
description: Monitor CPU load average.
weight: 18
---

# CPU Check

Reads system load average, normalizes it per CPU core, and compares against a threshold.

## Configuration

```php
'thresholds' => [
    'cpu_load_per_core' => 2.0,
],
```

## Usage

```php
use Cbox\LaravelHealth\Checks\CpuCheck;

'checks' => [
    'readiness' => [
        CpuCheck::class,
    ],
],
```

## Behavior

- Reads 1m, 5m, and 15m load averages via [cboxdk/system-metrics](https://github.com/cboxdk/system-metrics)
- Divides 1-minute load by core count to get normalized load
- Returns `critical` when normalized load exceeds `cpu_load_per_core`
- Metadata: `load_1m`, `load_5m`, `load_15m`, `cores`, `normalized_1m`, `threshold_per_core`

## Related Documentation

- [Health Checks Overview](_index.md)
- [Memory Check](memory.md)
- [System Metrics Integration](../advanced/system-metrics-integration.md)
