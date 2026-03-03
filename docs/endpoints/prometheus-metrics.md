---
title: Prometheus Metrics
description: Prometheus-compatible metrics endpoint.
weight: 32
---

# Prometheus Metrics

The `/health/metrics` endpoint returns OpenMetrics-compatible output for Prometheus scraping.

## Configuration

```php
'metrics' => [
    'prometheus' => [
        'enabled' => env('HEALTH_PROMETHEUS_ENABLED', true),
        'namespace' => env('HEALTH_PROMETHEUS_NAMESPACE', 'app'),
    ],
],
```

The `namespace` prefixes all metric names. Default: `app`.

## Health Check Metrics

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `{ns}_health_check_status` | gauge | `check` | 1.0 = ok, 0.5 = warning, 0.0 = critical/unknown |
| `{ns}_health_check_duration_seconds` | gauge | `check` | Check execution time |

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

## Container Metrics

When running inside a container, additional metrics are exposed:

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
    bearer_token: 'your-secret-token'
    static_configs:
      - targets: ['your-app:80']
```

## Disabling System Metrics

Toggle individual system metric groups in `config/health.php`:

```php
'metrics' => [
    'system' => [
        'cpu'     => true,
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
- [System Metrics Integration](../advanced/system-metrics-integration.md)
