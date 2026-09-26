---
title: Configuration Reference
description: Every Health for Laravel config key, its default, and how values are validated.
weight: 21
---

# Configuration Reference

All configuration lives in `config/health.php`. Publish it with:

```bash
php artisan vendor:publish --tag="health-config"
```

## Global Toggle

```php
'enabled' => env('HEALTH_ENABLED', true),
```

Set `HEALTH_ENABLED=false` to register no health routes at all.

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

All paths are relative to the prefix. Override the prefix with `HEALTH_PREFIX`. Setting an endpoint's `enabled` to `false` removes its route. Each route is named `health.<endpoint>` (for example `health.readiness`).

Routes are registered when the application boots, so config changes made at runtime (for example in a test) don't add or remove routes.

## Security

```php
'security' => [
    'token' => env('HEALTH_TOKEN'),
    'allowed_ips' => is_string($allowedIps = env('HEALTH_ALLOWED_IPS')) && $allowedIps !== ''
        ? array_map(trim(...), explode(',', $allowedIps))
        : null,
    'public_endpoints' => ['liveness'],
],

'middleware' => ['api'],
```

`middleware` is the stack applied to every health route. A single string (for example `'web'`) is treated as a one-item list. See [Endpoint Security](../security/endpoint-security.md) for token auth, IP allowlists, and custom auth callbacks.

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

Each probe runs its own list of check classes. Liveness should only contain checks where a restart fixes the problem. Dependency failures belong on readiness. See [Kubernetes Probes](../endpoints/kubernetes-probes.md#cascading-failures) for detailed guidance.

## Check-Specific Configuration

```php
'checks_config' => [
    'database'    => ['connection' => null],
    'cache'       => ['store' => null],
    'queue'       => ['connection' => null],
    'storage'     => ['disk' => 'local'],
    'redis'       => ['connection' => 'default'],
    'environment' => ['required' => []],
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
        'memory'  => true,
        'load'    => true,
        'storage' => true,
        'network' => true,
    ],
],
```

- `prometheus.enabled` — `false` removes the Prometheus route (`/health/metrics`). The JSON metrics endpoint is not affected. `endpoints.metrics.enabled = false` does the same.
- `prometheus.namespace` — prefixes all Prometheus metric names (e.g. `app_health_check_status`). It must match `^[a-zA-Z_][a-zA-Z0-9_]*$` (letters, digits and underscores, not starting with a digit).
- `system.*` — turns metric groups on or off in the Prometheus, JSON and dashboard output. See [System Metrics](../core-concepts/system-metrics.md).

## Thresholds

```php
'thresholds' => [
    'disk_space_percent' => 90,
    'memory_percent'     => 90,
    'cpu_load_per_core'  => 2.0,
],
```

The [Disk Space](../health-checks/disk-space.md), [Memory](../health-checks/memory.md) and [CPU](../health-checks/cpu.md) checks report `critical` when a value reaches or exceeds its threshold.

## Caching

```php
'cache' => [
    'enabled' => env('HEALTH_CACHE_ENABLED', true),
    'ttl'     => env('HEALTH_CACHE_TTL', 10),
    'store'   => null,
],
```

See [Caching](../core-concepts/caching.md) for details.

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `HEALTH_ENABLED` | `true` | Register the health routes |
| `HEALTH_PREFIX` | `health` | URL prefix for all endpoints |
| `HEALTH_TOKEN` | `null` | Bearer token for authentication |
| `HEALTH_ALLOWED_IPS` | `null` | Comma-separated IP/CIDR allowlist |
| `HEALTH_CACHE_ENABLED` | `true` | Cache check reports |
| `HEALTH_CACHE_TTL` | `10` | Cache TTL in seconds |
| `HEALTH_PROMETHEUS_ENABLED` | `true` | Register the Prometheus endpoint |
| `HEALTH_PROMETHEUS_NAMESPACE` | `app` | Prometheus metric name prefix |

## Validation

The package reads its config through typed accessors. Config is often fed from `env()`, so values arrive as strings; the accessors accept the obvious conversions and reject everything else instead of silently casting it.

| Expected type | Accepted | Examples of keys |
|---------------|----------|------------------|
| Boolean | `true`/`false`, and strings or integers PHP's boolean filter understands (`"true"`, `"false"`, `"1"`, `"0"`, `"on"`, `"off"`, `"yes"`, `"no"`) | `enabled`, `endpoints.*.enabled`, `metrics.prometheus.enabled`, `metrics.system.*`, `cache.enabled` |
| Integer | integers and integer strings (`"10"`) | `cache.ttl`, `checks_config.schedule.max_age_minutes` |
| Number | integers, floats and numeric strings (`"2.5"`) | `thresholds.*` |
| String | strings | `endpoints.prefix`, `endpoints.*.path`, `metrics.prometheus.namespace`, `checks_config.storage.disk`, `checks_config.redis.connection` |
| String or `null` | strings, `null` | `security.token`, `cache.store`, `checks_config.database.connection`, `checks_config.cache.store`, `checks_config.queue.connection` |
| List of strings | an array of strings, or a single string (treated as one item) | `middleware`, `checks.<endpoint>`, `security.public_endpoints`, `checks_config.environment.required` |
| List of strings or `null` | as above, or `null` | `security.allowed_ips` |

A value of the wrong type — for example `HEALTH_CACHE_TTL=ten`, or a non-string entry in `health.checks.readiness` — throws `Cbox\LaravelHealth\Exceptions\InvalidConfigurationException` with a message naming the key:

```
Configuration value [health.cache.ttl] must be an integer, string given.
```

An invalid Prometheus namespace (for example `my-app`) throws the same exception.

Values that a check reads while it runs (`checks_config.*`, `thresholds.*`) are the exception to "throws": the runner catches the error, so that check reports `critical` with the same message.

### Exceptions

Every exception the package throws implements the marker interface `Cbox\LaravelHealth\Exceptions\HealthException`, so you can catch them in one place:

| Exception | Meaning |
|-----------|---------|
| `InvalidConfigurationException` | A config value has the wrong type, or the Prometheus namespace is invalid |
| `InvalidCheckException` | A configured check class doesn't exist or doesn't implement `HealthCheck` |

Both extend `InvalidArgumentException`. The runner doesn't throw `InvalidCheckException` during a request: a missing or invalid check class (or one the container can't build) becomes a `critical` result named after the class, carrying the exception's message. A typo shows up in the report instead of as a `500`.

## Related Documentation

- [Health Checks](../health-checks/_index.md)
- [Endpoints](../endpoints/_index.md)
- [Endpoint Security](../security/endpoint-security.md)
- [Caching](../core-concepts/caching.md)
