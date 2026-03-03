---
title: Dashboard
description: HTML health status dashboard.
weight: 34
---

# Dashboard

Laravel Health includes an optional HTML dashboard at `/health/ui` showing real-time health status and system metrics.

## Enable the Dashboard

The dashboard is disabled by default. Enable it in `config/health.php`:

```php
'endpoints' => [
    'ui' => ['path' => '/ui', 'enabled' => true],
],
```

## Features

- Hostname displayed in the header — identifies which host served the page
- Liveness and readiness check results with status indicators
- System metrics overview (CPU, memory, disk, network)
- Auto-refreshing display (10s interval)
- Works with token and IP authentication

## Single-Host Environments

The dashboard is designed for single-host deployments (Forge, Ploi, standalone servers) where every request hits the same machine. In horizontally scaled environments (Kubernetes, load-balanced clusters), each refresh may hit a different host, making the dashboard unreliable for monitoring a specific instance.

For multi-host observability, use the [Prometheus metrics](prometheus-metrics.md) endpoint with a proper monitoring stack (Prometheus + Grafana), or query [JSON metrics](json-metrics.md) which includes a `hostname` field to identify the responding host.

## Authentication

The dashboard respects the same security configuration as other endpoints. If you have a token configured, access it at:

```
/health/ui?token=your-secret-token
```

## Related Documentation

- [Endpoints Overview](_index.md)
- [Security](../advanced/security.md)
- [Configuration](../configuration.md)
