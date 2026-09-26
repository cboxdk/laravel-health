---
title: Architecture
description: The request flow through Health for Laravel, from route to health report.
weight: 31
---

# Architecture

## Request Flow

```
HTTP request
  │
  ▼
Route  (prefix + path from health.endpoints.<name>, one route per enabled endpoint)
  │  middleware: health.middleware (default ['api'])
  │              → AllowIps              (health.security.allowed_ips)
  │              → EndpointAuth:<name>   (public_endpoints → token → auth callback)
  ▼
Controller  (one per endpoint)
  │
  ▼
RunsHealthChecks::run(EndpointType)      ← bound to HealthCheckRunner by default
  │
  ├─ cached report for this endpoint?  → return it   (health.cache.*)
  │
  ├─ for each class in health.checks.<endpoint>:
  │     container->make($class) → HealthCheck::run() → CheckResult (+ duration)
  │
  ▼
HealthReport::fromResults()  (status = worst check status; no checks = ok)
  │
  ▼
Response
```

## Pieces

**Routes** — `routes/health.php` registers one route per enabled endpoint under `health.endpoints.prefix`, named `health.<endpoint>`. The whole group gets the `health.middleware` stack plus `AllowIps`; each route gets `EndpointAuth` with its endpoint name. See [Endpoint Security](../security/endpoint-security.md).

**Controllers** — thin. Each one resolves `Cbox\LaravelHealth\Contracts\RunsHealthChecks` and turns the report into a response. None of them run checks directly.

**The runner** — `RunsHealthChecks` has one method, `run(EndpointType $type): HealthReport`. The default implementation, `Services\HealthCheckRunner`, reads the check classes from `health.checks.<endpoint>`, builds each one through the service container (so checks can use constructor injection), runs it, and records its duration. A check that throws, a class that doesn't exist, or a class that doesn't implement `HealthCheck` becomes a `critical` result instead of an error. You can decorate or replace the runner — see [Replacing the Runner](../extension-points/replacing-the-runner.md).

**Checks** — classes implementing `Contracts\HealthCheck` (`name()` and `run(): CheckResult`). See [Health Checks](../health-checks/_index.md) and [Custom Checks](../extension-points/custom-checks.md).

**Results and reports** — a `CheckResult` has a name, a `Status` (`ok`, `warning`, `critical`, `unknown`), a message, a duration and metadata. `HealthReport::fromResults()` aggregates them: the report status is the worst individual status, ranked `critical` > `unknown` > `warning` > `ok`. An endpoint with no checks is `ok`.

**Caching** — the runner caches each endpoint's report for `health.cache.ttl` seconds. See [Caching](caching.md).

## Endpoints and What They Run

| Endpoint | Runs | Response |
|----------|------|----------|
| Liveness, readiness, startup | That endpoint's checks | JSON report. `200` when the status is `ok` or `warning`, `503` when it is `critical` or `unknown` |
| Status | Liveness and readiness checks, plus system metrics | JSON with both reports, system metrics and app info. Always `200`; the top-level `status` is the readiness status |
| Metrics (Prometheus) | Liveness and readiness checks, plus system metrics | Prometheus text format. Always `200` |
| JSON | System metrics only | JSON. Always `200` |
| UI (dashboard) | Liveness and readiness checks, plus system metrics | HTML page |

The `health:check` Artisan command also goes through `RunsHealthChecks`. It runs liveness and readiness by default and exits non-zero when either report is `critical` or `unknown`.

## System Metrics

CPU load, memory, disk, network, uptime and container data come from [cboxdk/system-metrics](https://github.com/cboxdk/system-metrics). `Services\SystemMetricsService` collects a snapshot for the status, JSON and dashboard endpoints, and `Services\PrometheusRenderer` reads the same snapshot for the Prometheus output. The CPU, memory and disk space checks call `cboxdk/system-metrics` directly. See [System Metrics](system-metrics.md).

## Related Documentation

- [Endpoints](../endpoints/_index.md)
- [Configuration Reference](../configuration/reference.md)
- [Replacing the Runner](../extension-points/replacing-the-runner.md)
