---
title: Dashboard
description: HTML health status dashboard.
weight: 54
---

# Dashboard

Health for Laravel includes an optional HTML dashboard at `/health/ui` showing the current health status and system metrics.

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
- System info, CPU load, memory, disk usage, network interfaces, container limits and uptime (subject to the `metrics.system` toggles)
- Reloads itself every 10 seconds
- No external assets: the CSS and the refresh script are inline, and nothing is loaded from a CDN
- Works with token and IP authentication

## Customizing the View

Publish the view to change the template:

```bash
php artisan vendor:publish --tag="health-views"
```

The template is copied to `resources/views/vendor/health/dashboard.blade.php`.

## Single-Host Environments

The dashboard is designed for single-host deployments (Forge, Ploi, standalone servers) where every request hits the same machine. In horizontally scaled environments (Kubernetes, load-balanced clusters), each refresh may hit a different host, making the dashboard unreliable for monitoring a specific instance.

For multi-host observability, use the [Prometheus metrics](prometheus-metrics.md) endpoint with a proper monitoring stack (Prometheus + Grafana), or query [JSON metrics](json-metrics.md) which includes a `hostname` field to identify the responding host.

## Authentication

The dashboard respects the same security configuration as other endpoints. If you have a token configured, access it at:

```
/health/ui?token=your-secret-token
```

The auto-refresh reloads the same URL, so the token stays in place. Keep in mind that a token in the URL can end up in browser history and access logs.

## Related Documentation

- [Endpoints Overview](_index.md)
- [Endpoint Security](../security/endpoint-security.md)
- [Configuration Reference](../configuration/reference.md)
