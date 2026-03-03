---
title: Security
description: Secure health endpoints with tokens, IP allowlists, and custom auth.
weight: 42
---

# Security

Laravel Health supports three authentication methods: bearer tokens, IP allowlists, and custom auth callbacks.

## Token Authentication

Set a token via environment variable:

```env
HEALTH_TOKEN=your-secret-token
```

Authenticate with a query parameter or `Authorization` header:

```bash
# Query parameter
curl "http://localhost/health/ready?token=your-secret-token"

# Bearer token
curl -H "Authorization: Bearer your-secret-token" http://localhost/health/ready
```

## IP Allowlist

Restrict access by IP address:

```env
HEALTH_ALLOWED_IPS=10.0.0.1,10.0.0.2,172.16.0.0
```

When configured, requests from IPs not in the list receive a `403` response. This check runs before token authentication.

## Custom Auth Callback

Register a callback in a service provider for custom authorization logic:

```php
use Cbox\LaravelHealth\LaravelHealth;

public function boot(): void
{
    LaravelHealth::auth(function ($request) {
        return $request->user()?->isAdmin() ?? false;
    });
}
```

The callback receives the `Illuminate\Http\Request` and should return `bool`. It runs as a fallback when no token is configured or the token doesn't match.

## Public Endpoints

By default, the liveness endpoint is public (no auth required):

```php
'security' => [
    'public_endpoints' => ['liveness'],
],
```

Add or remove endpoint names to control which endpoints skip authentication.

## Auth Flow

1. Check if endpoint is in `public_endpoints` — if yes, allow
2. Check if request token matches `HEALTH_TOKEN` — if yes, allow
3. Check custom auth callback — if returns `true`, allow
4. If no callback is registered, allow in `local` environment only
5. Otherwise, return `403`

## Middleware

All endpoints use the configured middleware stack:

```php
'middleware' => ['api'],
```

Add middleware (e.g. `auth:sanctum`) to apply additional authentication layers.

## Related Documentation

- [Configuration](../configuration.md)
- [Endpoints](../endpoints/_index.md)
- [Kubernetes Probes](../endpoints/kubernetes-probes.md)
