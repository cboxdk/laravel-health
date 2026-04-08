---
title: Kubernetes Probes
description: Configure Kubernetes liveness, readiness, and startup probes.
weight: 31
---

# Kubernetes Probes

Health for Laravel provides dedicated endpoints for each Kubernetes probe type.

## Probe Endpoints

| Probe | Path | Default Checks |
|-------|------|----------------|
| Liveness | `/health` | `DatabaseCheck` |
| Readiness | `/health/ready` | `DatabaseCheck`, `CacheCheck`, `QueueCheck`, `StorageCheck` |
| Startup | `/health/startup` | None |

## Kubernetes Deployment YAML

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: my-app
spec:
  template:
    spec:
      containers:
        - name: app
          livenessProbe:
            httpGet:
              path: /health
              port: 80
            initialDelaySeconds: 10
            periodSeconds: 15
          readinessProbe:
            httpGet:
              path: /health/ready
              port: 80
            initialDelaySeconds: 5
            periodSeconds: 10
          startupProbe:
            httpGet:
              path: /health/startup
              port: 80
            failureThreshold: 30
            periodSeconds: 5
```

## Probe Design

Understanding the difference between liveness and readiness is critical. Misconfigured probes are the most common cause of self-inflicted outages in Kubernetes.

**Liveness** — "Is the process stuck?" If this fails, Kubernetes **restarts the container**. Only check things intrinsic to the process itself. The default `DatabaseCheck` verifies the app can reach its primary datastore — that's usually sufficient.

**Readiness** — "Can this pod serve traffic?" If this fails, the pod is removed from the Service load balancer but **keeps running**. Include all dependencies the app needs to handle a request: database, cache, queue, external APIs.

**Startup** — "Has the app finished booting?" Runs once. Prevents premature liveness kills on slow-starting apps (e.g. large migration, cache warm-up). No checks by default.

## Cascading Failures

> A liveness check that depends on an external service can take down your entire application.

When Redis goes down and `RedisCheck` is on your **liveness** probe, Kubernetes restarts every pod. The pods come back, still can't reach Redis, get restarted again. Your app is now in a crash loop — not because *it* is broken, but because a dependency is temporarily unavailable.

**The rule**: liveness checks should only fail when the process itself is unhealthy and a restart would fix it. Dependency failures belong on **readiness**.

### What goes where

| Check | Liveness | Readiness | Why |
|-------|:--------:|:---------:|-----|
| `DatabaseCheck` | Yes | Yes | No DB = process can't function |
| `CacheCheck` | No | Yes | Cache outage is recoverable without restart |
| `QueueCheck` | No | Yes | Queue backlog doesn't mean process is stuck |
| `StorageCheck` | No | Yes | Disk issue won't be fixed by restarting |
| `RedisCheck` | No | Yes | Redis outage is external, restart won't help |
| `ScheduleCheck` | No | Yes | Stale heartbeat is informational |
| `CpuCheck` | No | Optional | High load is transient |
| `MemoryCheck` | No | Optional | OOM killer handles this already |
| `DiskSpaceCheck` | No | Optional | Restart won't free disk space |
| `EnvironmentCheck` | No | No | Belongs on startup — env vars don't change at runtime |
| External API check | **Never** | Yes | External failures must never trigger restarts |

### Example: safe configuration

```php
'checks' => [
    'liveness' => [
        DatabaseCheck::class,     // core dependency only
    ],
    'readiness' => [
        DatabaseCheck::class,
        CacheCheck::class,
        RedisCheck::class,
        QueueCheck::class,
        StorageCheck::class,
    ],
    'startup' => [
        EnvironmentCheck::class,  // validate env once at boot
    ],
],
```

If Redis goes down with this configuration: the readiness probe fails, Kubernetes stops sending traffic to the pod, but the pod **stays alive** and recovers automatically when Redis returns.

## Authentication

The liveness endpoint is public by default (configured in `security.public_endpoints`). For token-protected probes, pass the token as a query parameter in your probe config:

```yaml
readinessProbe:
  httpGet:
    path: /health/ready?token=your-secret-token
    port: 80
```

## Related Documentation

- [Endpoints Overview](_index.md)
- [Health Checks](../health-checks/_index.md)
- [Security](../advanced/security.md)
