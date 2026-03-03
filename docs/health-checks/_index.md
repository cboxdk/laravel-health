---
title: Health Checks
description: Overview of all built-in health checks.
weight: 10
---

# Health Checks

Laravel Health ships with 11 built-in checks. Each implements the `HealthCheck` contract and returns a `CheckResult` with status `ok`, `warning`, `critical`, or `unknown`.

## Available Checks

| Check | Class | What it does |
|-------|-------|-------------|
| [Database](database.md) | `DatabaseCheck` | Verifies database connectivity via PDO |
| [Cache](cache.md) | `CacheCheck` | Write/read/delete test key |
| [Queue](queue.md) | `QueueCheck` | Reports queue size |
| [Storage](storage.md) | `StorageCheck` | Write/delete test file on disk |
| [Redis](redis.md) | `RedisCheck` | Sends PING to Redis |
| [Environment](environment.md) | `EnvironmentCheck` | Verifies required env vars exist |
| [Schedule](schedule.md) | `ScheduleCheck` | Checks scheduler heartbeat freshness |
| [CPU](cpu.md) | `CpuCheck` | Load average normalized per core |
| [Memory](memory.md) | `MemoryCheck` | System memory usage (cgroup aware) |
| [Disk Space](disk-space.md) | `DiskSpaceCheck` | Mount point usage percentage |

## Choosing Checks per Endpoint

Not every check belongs on every probe. The wrong check on the wrong probe can cause cascading failures — see [Kubernetes Probes: Cascading Failures](../endpoints/kubernetes-probes.md#cascading-failures) for the full explanation.

**Liveness** — only checks that detect a stuck process. A restart should fix the problem. In most applications, `DatabaseCheck` alone is correct.

**Readiness** — all dependencies the app needs to serve a request. When a check fails here, the pod stops receiving traffic but stays alive, recovering automatically when the dependency returns.

**Startup** — one-time validation at boot (e.g. `EnvironmentCheck`). Does not run after the pod is ready.

```php
'checks' => [
    'liveness' => [
        DatabaseCheck::class,
    ],
    'readiness' => [
        DatabaseCheck::class,
        CacheCheck::class,
        QueueCheck::class,
        StorageCheck::class,
    ],
    'startup' => [
        EnvironmentCheck::class,
    ],
],
```

## Related Documentation

- [Kubernetes Probes](../endpoints/kubernetes-probes.md) — probe design strategy and cascading failure prevention
- [Configuration](../configuration.md)
- [Custom Checks](../advanced/custom-checks.md)
- [Endpoints](../endpoints/_index.md)
