---
title: Introduction
description: Health checks, Kubernetes probes, Prometheus metrics, and system monitoring for Laravel.
weight: 1
---

# Introduction

Health for Laravel adds health endpoints to a Laravel application: Kubernetes liveness, readiness and startup probes, a Prometheus scrape endpoint, JSON system metrics, and an optional HTML dashboard. It ships with 10 built-in health checks, and everything is configured from `config/health.php`.

## Features

- [Kubernetes Probes](endpoints/kubernetes-probes.md) — liveness, readiness, and startup endpoints
- [Prometheus Metrics](endpoints/prometheus-metrics.md) — Prometheus text format at `/health/metrics`
- [JSON Metrics](endpoints/json-metrics.md) — structured system metrics API
- [HTML Dashboard](endpoints/dashboard.md) — optional status page with no external assets
- [10 Built-in Checks](health-checks/_index.md) — database, cache, queue, storage, Redis, environment, schedule, CPU, memory, disk space
- [Custom Checks](extension-points/custom-checks.md) — implement the `HealthCheck` contract
- [Replacing the Runner](extension-points/replacing-the-runner.md) — decorate or swap the `RunsHealthChecks` contract
- [Token & IP Auth](security/endpoint-security.md) — protect endpoints with bearer tokens and IP allowlists
- [Response Caching](core-concepts/caching.md) — configurable TTL to reduce overhead
- [Container Awareness](core-concepts/system-metrics.md) — automatic cgroup detection for Docker/Kubernetes
- [Testing Helpers](getting-started/testing.md) — fake the runner and system metrics in your tests

## How It Works

The package registers health check endpoints under a configurable prefix (default: `/health`). Each probe endpoint runs its own set of checks and returns:

- `200` — every check is `ok` or `warning`
- `503` — at least one check is `critical` or `unknown`

You assign checks to probes based on their purpose:

- **Liveness** (`/health`) — is the process stuck? Failure triggers a container restart. Keep this minimal — typically just the database.
- **Readiness** (`/health/ready`) — can this pod serve traffic? Failure removes the pod from the load balancer but keeps it running. Include all dependencies here.
- **Startup** (`/health/startup`) — has the app finished booting? Kubernetes stops calling it once it succeeds.

This distinction matters. A Redis check on liveness means a Redis outage restarts all your pods — turning a dependency blip into a full application outage. See [Kubernetes Probes](endpoints/kubernetes-probes.md) for detailed guidance, and [Architecture](core-concepts/architecture.md) for how a request flows through the package.

## Documentation

- **Getting started** — [Requirements](requirements.md), [Installation](getting-started/installation.md), [Quick Start](quickstart.md), [Testing](getting-started/testing.md)
- **Configuration** — [Configuration Reference](configuration/reference.md)
- **Core concepts** — [Architecture](core-concepts/architecture.md), [Caching](core-concepts/caching.md), [System Metrics](core-concepts/system-metrics.md)
- **Health checks** — [Overview](health-checks/_index.md) and one page per built-in check
- **Endpoints** — [Overview](endpoints/_index.md), [Kubernetes Probes](endpoints/kubernetes-probes.md), [Prometheus Metrics](endpoints/prometheus-metrics.md), [JSON Metrics](endpoints/json-metrics.md), [Dashboard](endpoints/dashboard.md)
- **Extension points** — [Custom Checks](extension-points/custom-checks.md), [Replacing the Runner](extension-points/replacing-the-runner.md)
- **Security** — [Endpoint Security](security/endpoint-security.md)
- **Upgrading** — [Upgrade guide](../UPGRADING.md), [Changelog](../CHANGELOG.md)
