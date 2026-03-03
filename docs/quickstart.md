---
title: Quick Start
description: Get up and running with Laravel Health in minutes.
weight: 3
---

# Quick Start

## Default Endpoints

After installation, these endpoints are available:

| Endpoint | Path | Purpose |
|----------|------|---------|
| Liveness | `/health` | Kubernetes liveness probe |
| Readiness | `/health/ready` | Kubernetes readiness probe |
| Startup | `/health/startup` | Kubernetes startup probe |
| Status | `/health/status` | Full status overview |
| Metrics | `/health/metrics` | Prometheus metrics |
| JSON | `/health/metrics/json` | JSON system metrics |

The UI dashboard is disabled by default.

## Add a Check

Assign checks to probe endpoints in `config/health.php`. Liveness should be minimal (restart-worthy failures only). Readiness includes all dependencies:

```php
use Cbox\LaravelHealth\Checks\RedisCheck;

'checks' => [
    'liveness' => [
        DatabaseCheck::class,         // only core dependency
    ],
    'readiness' => [
        DatabaseCheck::class,
        CacheCheck::class,
        QueueCheck::class,
        StorageCheck::class,
        RedisCheck::class,            // dependency checks go here
    ],
],
```

Why this split matters: if Redis goes down and `RedisCheck` is on liveness, Kubernetes restarts every pod — turning a Redis blip into a full outage. On readiness, pods stop receiving traffic but stay alive and recover when Redis returns. See [Kubernetes Probes](endpoints/kubernetes-probes.md) for the full guide.

## Secure Endpoints

Set a bearer token via environment variable:

```env
HEALTH_TOKEN=your-secret-token
```

Then authenticate requests:

```bash
curl -H "Authorization: Bearer your-secret-token" http://localhost/health/ready
```

The liveness endpoint is public by default. See [Security](advanced/security.md) for IP allowlists and custom auth.

## Enable the Dashboard

In `config/health.php`:

```php
'endpoints' => [
    'ui' => ['path' => '/ui', 'enabled' => true],
],
```

Visit `/health/ui` to see the HTML dashboard with real-time health status and system metrics.

## Related Documentation

- [Configuration](configuration.md)
- [Health Checks](health-checks/_index.md)
- [Endpoints](endpoints/_index.md)
- [Security](advanced/security.md)
