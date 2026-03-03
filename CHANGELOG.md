# Changelog

All notable changes to `laravel-health` will be documented in this file.

## 1.0.0 - Unreleased

### Added
- Kubernetes liveness, readiness, and startup probe endpoints
- Prometheus-compatible metrics endpoint with health check and system metrics
- JSON system metrics endpoint
- HTML dashboard with real-time status display
- 11 built-in health checks: database, cache, queue, storage, Redis, environment, schedule, CPU, memory, disk space
- System metrics via cboxdk/system-metrics with automatic cgroup detection
- Container-aware memory and CPU metrics (cgroup v1/v2)
- Token and IP-based endpoint authentication
- Custom auth callback support
- Response caching with configurable TTL
- Hostname identification in status, JSON metrics, and dashboard responses
- Extensible architecture via HealthCheck contract
- Full documentation with probe design guidance
