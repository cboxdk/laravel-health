---
title: Disk Space Check
description: Monitor disk usage per mount point.
weight: 50
---

# Disk Space Check

Reads all mount points and reports critical when any exceeds the usage threshold.

## Configuration

```php
'thresholds' => [
    'disk_space_percent' => 90,
],
```

## Usage

```php
use Cbox\LaravelHealth\Checks\DiskSpaceCheck;

'checks' => [
    'readiness' => [
        DiskSpaceCheck::class,
    ],
],
```

## Behavior

- Reads storage metrics for all mount points via [cboxdk/system-metrics](https://github.com/cboxdk/system-metrics)
- Returns `critical` when any mount point reaches or exceeds `disk_space_percent`
- Returns `unknown` when storage metrics can't be read
- Metadata: `threshold`, `critical_mounts` (list of mounts exceeding threshold)

## Related Documentation

- [Health Checks Overview](_index.md)
- [CPU Check](cpu.md)
- [System Metrics](../core-concepts/system-metrics.md)
