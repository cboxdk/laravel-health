---
title: Introduction
description: Health checks, Kubernetes probes, Prometheus metrics, and system monitoring for Laravel.
weight: 1
---

# Introduction

Health for Laravel provides production-ready health monitoring for Laravel applications. It ships with Kubernetes probe endpoints, Prometheus-compatible metrics, and 11 built-in health checks — all configurable from a single config file.

## Features

- [Kubernetes Probes](endpoints/kubernetes-probes.md) — liveness, readiness, and startup endpoints
- [Prometheus Metrics](endpoints/prometheus-metrics.md) — OpenMetrics-compatible `/health/metrics`
- [JSON Metrics](endpoints/json-metrics.md) — structured system metrics API
- [HTML Dashboard](endpoints/dashboard.md) — optional real-time status UI
- [11 Built-in Checks](health-checks/_index.md) — database, cache, queue, storage, Redis, environment, schedule, CPU, memory, disk space
- [Custom Checks](advanced/custom-checks.md) — implement the `HealthCheck` contract
- [Token & IP Auth](advanced/security.md) — protect endpoints with bearer tokens and IP allowlists
- [Response Caching](advanced/caching.md) — configurable TTL to reduce overhead
- [Container Awareness](advanced/system-metrics-integration.md) — automatic cgroup detection for Docker/Kubernetes

## How It Works

The package registers health check endpoints under a configurable prefix (default: `/health`). Each endpoint runs its own set of checks and returns:

- `200` — all checks pass
- `503` — one or more checks fail

You assign checks to probes based on their purpose:

- **Liveness** (`/health`) — is the process stuck? Failure triggers a container restart. Keep this minimal — typically just the database.
- **Readiness** (`/health/ready`) — can this pod serve traffic? Failure removes the pod from the load balancer but keeps it running. Include all dependencies here.
- **Startup** (`/health/startup`) — has the app finished booting? Runs once.

This distinction matters. A Redis check on liveness means a Redis outage restarts all your pods — turning a dependency blip into a full application outage. See [Kubernetes Probes](endpoints/kubernetes-probes.md) for detailed guidance.

## Related Documentation

- [Installation](installation.md)
- [Quick Start](quickstart.md)
- [Configuration](configuration.md)
