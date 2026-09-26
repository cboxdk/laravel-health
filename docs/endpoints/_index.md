---
title: Endpoints
description: Overview of all health check endpoints.
weight: 50
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

The liveness, readiness and startup endpoints return:

- `200` — all checks pass (status `ok` or `warning`)
- `503` — one or more checks are `critical` or `unknown`

The status, Prometheus and JSON endpoints always return `200` once the request is authorized; the health state is in the body. A request that fails the IP allowlist or authentication gets `403` (see [Endpoint Security](../security/endpoint-security.md)).

See [Architecture](../core-concepts/architecture.md) for which checks each endpoint runs.

## Customizing Paths

Override any path in `config/health.php`:

```php
'endpoints' => [
    'prefix' => env('HEALTH_PREFIX', 'health'),
    'readiness' => ['path' => '/readyz', 'enabled' => true],
],
```

## Hostname Identification

The `/health/metrics/json` endpoint includes a top-level `hostname` field and `/health/status` includes `app.hostname`, identifying which host or pod served the request. The dashboard shows it in its header. This is essential in horizontally scaled deployments where a load balancer routes to any instance.

The liveness, readiness, and startup probe endpoints intentionally omit the hostname to stay lightweight.

## Disabling Endpoints

Set `enabled` to `false` for any endpoint you don't need:

```php
'endpoints' => [
    'metrics' => ['path' => '/metrics', 'enabled' => false],
],
```

A disabled endpoint has no route, so requests to its path get a `404`. The Prometheus endpoint can also be switched off with `HEALTH_PROMETHEUS_ENABLED=false`, and `HEALTH_ENABLED=false` removes every health route.

## Related Documentation

- [Configuration Reference](../configuration/reference.md)
- [Endpoint Security](../security/endpoint-security.md)
- [Kubernetes Probes](kubernetes-probes.md)
