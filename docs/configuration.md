---
title: Configuration
description: Complete reference for all Laravel Health configuration options.
weight: 5
---

# Configuration

All configuration lives in `config/health.php`. Publish it with:

```bash
php artisan vendor:publish --tag="health-config"
```

## Global Toggle

```php
'enabled' => env('HEALTH_ENABLED', true),
```

Set `HEALTH_ENABLED=false` to disable all health endpoints.

## Endpoints

```php
'endpoints' => [
    'prefix' => env('HEALTH_PREFIX', 'health'),
    'liveness'  => ['path' => '/',             'enabled' => true],
    'readiness' => ['path' => '/ready',        'enabled' => true],
    'startup'   => ['path' => '/startup',      'enabled' => true],
    'status'    => ['path' => '/status',       'enabled' => true],
    'metrics'   => ['path' => '/metrics',      'enabled' => true],
    'json'      => ['path' => '/metrics/json', 'enabled' => true],
    'ui'        => ['path' => '/ui',           'enabled' => false],
],
```

All paths are relative to the prefix. Override with `HEALTH_PREFIX`.

## Security

```php
'security' => [
    'token' => env('HEALTH_TOKEN'),
    'allowed_ips' => env('HEALTH_ALLOWED_IPS')
        ? explode(',', env('HEALTH_ALLOWED_IPS'))
        : null,
    'public_endpoints' => ['liveness'],
],

'middleware' => ['api'],
```

See [Security](advanced/security.md) for details on token auth, IP allowlists, and custom auth callbacks.

## Health Checks

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
    'startup' => [],
],
```

Each probe runs its own set of checks. Liveness should only contain checks where a restart fixes the problem. Dependency failures belong on readiness. See [Kubernetes Probes](endpoints/kubernetes-probes.md#cascading-failures) for detailed guidance.

## Check-Specific Configuration

```php
'checks_config' => [
    'database'    => ['connection' => null],
    'cache'       => ['store' => null],
    'queue'       => ['connection' => null],
    'storage'     => ['disk' => 'local'],
    'redis'       => ['connection' => 'default'],
    'environment' => ['required' => [], 'optional' => []],
    'schedule'    => ['max_age_minutes' => 5],
],
```

`null` values use the Laravel default connection/store.

## Metrics

```php
'metrics' => [
    'prometheus' => [
        'enabled' => env('HEALTH_PROMETHEUS_ENABLED', true),
        'namespace' => env('HEALTH_PROMETHEUS_NAMESPACE', 'app'),
    ],
    'system' => [
        'cpu'     => true,
        'memory'  => true,
        'load'    => true,
        'storage' => true,
        'network' => true,
    ],
],
```

The `namespace` prefixes all Prometheus metric names (e.g. `app_health_check_status`).

## Thresholds

```php
'thresholds' => [
    'disk_space_percent' => 90,
    'memory_percent'     => 90,
    'cpu_load_per_core'  => 2.0,
],
```

Checks report `critical` when these thresholds are exceeded.

## Caching

```php
'cache' => [
    'enabled' => env('HEALTH_CACHE_ENABLED', true),
    'ttl'     => env('HEALTH_CACHE_TTL', 10),
    'store'   => null,
],
```

See [Caching](advanced/caching.md) for details.

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `HEALTH_ENABLED` | `true` | Enable/disable all endpoints |
| `HEALTH_PREFIX` | `health` | URL prefix for all endpoints |
| `HEALTH_TOKEN` | `null` | Bearer token for authentication |
| `HEALTH_ALLOWED_IPS` | `null` | Comma-separated IP allowlist |
| `HEALTH_CACHE_ENABLED` | `true` | Enable response caching |
| `HEALTH_CACHE_TTL` | `10` | Cache TTL in seconds |
| `HEALTH_PROMETHEUS_ENABLED` | `true` | Enable Prometheus metrics |
| `HEALTH_PROMETHEUS_NAMESPACE` | `app` | Prometheus metric name prefix |

## Related Documentation

- [Health Checks](health-checks/_index.md)
- [Endpoints](endpoints/_index.md)
- [Security](advanced/security.md)
- [Caching](advanced/caching.md)
