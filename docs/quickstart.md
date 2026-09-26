---
title: Quick Start
description: Get up and running with Health for Laravel in minutes.
weight: 2
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
use Cbox\LaravelHealth\Checks\CacheCheck;
use Cbox\LaravelHealth\Checks\DatabaseCheck;
use Cbox\LaravelHealth\Checks\QueueCheck;
use Cbox\LaravelHealth\Checks\RedisCheck;
use Cbox\LaravelHealth\Checks\StorageCheck;

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

## Run Checks from the CLI

```bash
php artisan health:check                       # liveness and readiness
php artisan health:check --endpoint=readiness  # one endpoint
```

The command prints each check's status and exits non-zero when an endpoint reports `critical` or `unknown`. `--endpoint` accepts `liveness`, `readiness` or `startup`.

## Secure Endpoints

Set a bearer token via environment variable:

```env
HEALTH_TOKEN=your-secret-token
```

Then authenticate requests:

```bash
curl -H "Authorization: Bearer your-secret-token" http://localhost/health/ready
```

The liveness endpoint is public by default. Every other endpoint needs the token, a custom auth callback, or the `local` environment — otherwise it returns `403`. See [Endpoint Security](security/endpoint-security.md) for IP allowlists and custom auth.

## Enable the Dashboard

In `config/health.php`:

```php
'endpoints' => [
    'ui' => ['path' => '/ui', 'enabled' => true],
],
```

Visit `/health/ui` to see the HTML dashboard with the current health status and system metrics.

## Related Documentation

- [Configuration Reference](configuration/reference.md)
- [Health Checks](health-checks/_index.md)
- [Endpoints](endpoints/_index.md)
- [Endpoint Security](security/endpoint-security.md)
- [Testing](getting-started/testing.md)
