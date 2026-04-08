---
title: Caching
description: Configure response caching for health check endpoints.
weight: 43
---

# Caching

Health for Laravel caches check results to reduce overhead from frequent probe requests.

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
| `enabled` | `true` | Enable/disable response caching |
| `ttl` | `10` | Cache duration in seconds |
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
- **Development**: set `HEALTH_CACHE_ENABLED=false` for instant feedback

## Cache Keys

Health check results are cached per endpoint type. The cache is shared across all requests to the same endpoint within the TTL window.

## Related Documentation

- [Configuration](../configuration.md)
- [Endpoints](../endpoints/_index.md)
