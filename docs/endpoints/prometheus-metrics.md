---
title: Prometheus Metrics
description: Prometheus-compatible metrics endpoint.
weight: 52
---

# Prometheus Metrics

The `/health/metrics` endpoint returns metrics in the Prometheus text exposition format (`Content-Type: text/plain; version=0.0.4`) for scraping.

## Configuration

```php
'metrics' => [
    'prometheus' => [
        'enabled' => env('HEALTH_PROMETHEUS_ENABLED', true),
        'namespace' => env('HEALTH_PROMETHEUS_NAMESPACE', 'app'),
    ],
],
```

The `namespace` prefixes all metric names. Default: `app`. It must match `^[a-zA-Z_][a-zA-Z0-9_]*$`; anything else throws an `InvalidConfigurationException` (see [Configuration Reference](../configuration/reference.md#validation)).

Set `HEALTH_PROMETHEUS_ENABLED=false` to remove the endpoint entirely. The JSON metrics endpoint is not affected.

## Health Check Metrics

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `{ns}_health_check_status` | gauge | `check` | 1.0 = ok, 0.5 = warning, 0.0 = critical/unknown |
| `{ns}_health_check_duration_seconds` | gauge | `check` | Check execution time |

The health check metrics cover the liveness and readiness checks. A check that appears on both endpoints is reported once, and startup checks are not included. Reports come from the [cache](../core-concepts/caching.md) when it is enabled.

## System Metrics

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `{ns}_system_cpu_load_1m` | gauge | — | 1-minute load average |
| `{ns}_system_cpu_load_5m` | gauge | — | 5-minute load average |
| `{ns}_system_cpu_load_15m` | gauge | — | 15-minute load average |
| `{ns}_system_memory_used_bytes` | gauge | — | Memory used |
| `{ns}_system_memory_total_bytes` | gauge | — | Memory total |
| `{ns}_system_memory_usage_ratio` | gauge | — | Memory usage 0–1 |
| `{ns}_system_disk_used_bytes` | gauge | `mountpoint` | Disk used per mount |
| `{ns}_system_disk_total_bytes` | gauge | `mountpoint` | Disk total per mount |
| `{ns}_system_disk_usage_ratio` | gauge | `mountpoint` | Disk usage 0–1 |
| `{ns}_system_network_rx_bytes_total` | counter | `interface` | Bytes received |
| `{ns}_system_network_tx_bytes_total` | counter | `interface` | Bytes transmitted |
| `{ns}_system_uptime_seconds` | gauge | — | System uptime |

Inside a container, the memory metrics use the cgroup limit and usage when available (see [System Metrics](../core-concepts/system-metrics.md)).

## Container Metrics

When running inside a container, additional metrics are exposed (each one only when the value is available):

| Metric | Type | Description |
|--------|------|-------------|
| `{ns}_container_memory_limit_bytes` | gauge | Container memory limit |
| `{ns}_container_memory_usage_bytes` | gauge | Container memory usage |
| `{ns}_container_cpu_quota` | gauge | Container CPU quota (cores) |
| `{ns}_container_cpu_throttled_total` | counter | CPU throttle count |
| `{ns}_container_oom_kills_total` | counter | OOM kill count |

## Prometheus Scrape Config

```yaml
scrape_configs:
  - job_name: 'laravel'
    metrics_path: '/health/metrics'
    authorization:
      type: Bearer
      credentials: 'your-secret-token'
    static_configs:
      - targets: ['your-app:80']
```

## Disabling System Metrics

Toggle individual system metric groups in `config/health.php`:

```php
'metrics' => [
    'system' => [
        'memory'  => true,
        'load'    => true,
        'storage' => false,
        'network' => false,
    ],
],
```

## Related Documentation

- [Endpoints Overview](_index.md)
- [JSON Metrics](json-metrics.md)
- [System Metrics](../core-concepts/system-metrics.md)
