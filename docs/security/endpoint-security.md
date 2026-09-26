---
title: Endpoint Security
description: Secure health endpoints with tokens, IP allowlists, and custom auth.
weight: 71
---

# Endpoint Security

Health for Laravel supports three ways to control access: bearer tokens, IP allowlists, and custom auth callbacks.

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

If the request has a `token` query parameter, that value is compared; otherwise the bearer token is. An array-valued query parameter (`?token[]=...`) is ignored and the bearer token is used instead. Tokens are compared in constant time.

Query-string tokens can end up in access logs and browser history. Prefer the `Authorization` header where the client supports it.

## IP Allowlist

Restrict access by IP address or IPv4 CIDR range:

```env
HEALTH_ALLOWED_IPS=10.0.0.1,10.0.0.2,172.16.0.0/12
```

When configured, requests from IPs not in the list receive a `403` response. The allowlist applies to **every** health endpoint, including public ones such as liveness, and runs before token authentication. If your Kubernetes probes are subject to the allowlist, include the addresses the kubelet connects from.

The client IP is Laravel's `$request->ip()`. Behind a load balancer or reverse proxy, configure Laravel's trusted proxies so it reflects the real client address.

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

The callback receives the `Illuminate\Http\Request` and should return `bool`. It runs as a fallback when no token is configured or the token doesn't match. The `LaravelHealth` facade exposes the same `auth()` method.

`$request->user()` is only populated when the health routes run a middleware stack that starts the session or authenticates the request (see [Middleware](#middleware)). With the default `api` stack, a session user is not available.

## Public Endpoints

By default, the liveness endpoint is public (no token or callback required):

```php
'security' => [
    'public_endpoints' => ['liveness'],
],
```

Add or remove endpoint names (`liveness`, `readiness`, `startup`, `status`, `metrics`, `json`, `ui`) to control which endpoints skip authentication. The IP allowlist still applies.

## Auth Flow

1. `AllowIps`: if `allowed_ips` is set and the client IP isn't in it, return `403`
2. `EndpointAuth`: if the endpoint is in `public_endpoints`, allow
3. If a token is configured and the request token matches, allow
4. If the auth callback returns `true`, allow
5. If no callback is registered, allow in the `local` environment only
6. Otherwise, return `403`

## Middleware

All endpoints use the configured middleware stack, followed by `AllowIps`:

```php
'middleware' => ['api'],
```

A single string (for example `'web'`) is accepted as a one-item list. Adding authentication middleware such as `auth:sanctum` applies it to every endpoint, including public ones — Kubernetes probes would then need to authenticate too.

## Related Documentation

- [Configuration Reference](../configuration/reference.md)
- [Endpoints](../endpoints/_index.md)
- [Kubernetes Probes](../endpoints/kubernetes-probes.md)
- [Testing](../getting-started/testing.md) — authorize endpoints in tests
