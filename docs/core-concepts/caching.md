---
title: Caching
description: Configure caching of health check reports.
weight: 32
---

# Caching

Health for Laravel caches check reports to reduce overhead from frequent probe requests.

## Configuration

```php
'cache' => [
    'enabled' => env('HEALTH_CACHE_ENABLED', true),
    'ttl'     => env('HEALTH_CACHE_TTL', 10),
    'store'   => null,
],
```

| Option | Default | Description |
|--------|---------|-------------|
| `enabled` | `true` | Enable/disable report caching |
| `ttl` | `10` | Cache duration in seconds (integer) |
| `store` | `null` | Cache store (`null` = default store) |

## Environment Variables

```env
HEALTH_CACHE_ENABLED=true
HEALTH_CACHE_TTL=10
```

## When to Adjust TTL

- **Kubernetes probes** polling every 10–15s: a TTL of 10s is appropriate
- **High-frequency monitoring**: lower the TTL to 5s for fresher data
- **Expensive checks**: raise the TTL to 30–60s to reduce load
- **Development and tests**: set `HEALTH_CACHE_ENABLED=false` for instant feedback

## What Is Cached

The runner caches the `HealthReport` for each endpoint type under the key `health:report:<endpoint>` (for example `health:report:readiness`). Every request that runs that endpoint's checks within the TTL window gets the same report — including the status, Prometheus and dashboard endpoints, which run the liveness and readiness checks, and the `health:check` command.

System metrics (CPU, memory, disk, network) are not cached; they are read on every request.

If the cache store is unavailable, the runner runs the checks and skips caching instead of failing. A broken cache backend therefore doesn't break the health endpoints — use [`CacheCheck`](../health-checks/cache.md) to detect it.

If you [replace the runner](../extension-points/replacing-the-runner.md) with your own implementation, caching is up to you. A decorator that delegates to `HealthCheckRunner` keeps it.

## Related Documentation

- [Configuration Reference](../configuration/reference.md)
- [Architecture](architecture.md)
- [Endpoints](../endpoints/_index.md)
