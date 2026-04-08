---
title: Endpoints
description: Overview of all health check endpoints.
weight: 30
---

# Endpoints

Health for Laravel registers 7 endpoints under a configurable prefix (default: `/health`).

## Available Endpoints

| Endpoint | Default Path | Default State | Purpose |
|----------|-------------|---------------|---------|
| Liveness | `/health` | Enabled | [Kubernetes liveness probe](kubernetes-probes.md) |
| Readiness | `/health/ready` | Enabled | [Kubernetes readiness probe](kubernetes-probes.md) |
| Startup | `/health/startup` | Enabled | [Kubernetes startup probe](kubernetes-probes.md) |
| Status | `/health/status` | Enabled | Full status with all check results |
| Metrics | `/health/metrics` | Enabled | [Prometheus metrics](prometheus-metrics.md) |
| JSON | `/health/metrics/json` | Enabled | [JSON system metrics](json-metrics.md) |
| UI | `/health/ui` | **Disabled** | [HTML dashboard](dashboard.md) |

## Response Codes

All probe endpoints return:

- `200` — all checks pass (status `ok` or `warning`)
- `503` — one or more checks are `critical` or `unknown`

## Customizing Paths

Override any path in `config/health.php`:

```php
'endpoints' => [
    'prefix' => env('HEALTH_PREFIX', 'health'),
    'readiness' => ['path' => '/readyz', 'enabled' => true],
],
```

## Hostname Identification

The `/health/status` and `/health/metrics/json` endpoints include a `hostname` field in their response, identifying which host or pod served the request. This is essential in horizontally scaled deployments where a load balancer routes to any instance.

The liveness, readiness, and startup probe endpoints intentionally omit the hostname to stay lightweight.

## Disabling Endpoints

Set `enabled` to `false` for any endpoint you don't need:

```php
'endpoints' => [
    'metrics' => ['path' => '/metrics', 'enabled' => false],
],
```

## Related Documentation

- [Configuration](../configuration.md)
- [Security](../advanced/security.md)
- [Kubernetes Probes](kubernetes-probes.md)
