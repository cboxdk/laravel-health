# Changelog

All notable changes to `laravel-health` will be documented in this file.

## 1.0.0 - 2026-03-20

### Added
- Kubernetes liveness, readiness, and startup probe endpoints
- Prometheus-compatible metrics endpoint with health check and system metrics
- JSON system metrics endpoint
- HTML dashboard with auto-refresh
- 10 built-in health checks: database, cache, queue, storage, Redis, environment, schedule, CPU, memory, disk space
- System metrics via cboxdk/system-metrics with automatic cgroup detection
- Container-aware memory and CPU metrics (cgroup v1/v2)
- Token and IP-based endpoint authentication with CIDR range support
- Custom auth callback support
- Response caching with configurable TTL
- Hostname identification in status, JSON metrics, and dashboard responses
- Extensible architecture via HealthCheck contract
- `health:check` artisan command for CLI health verification
- `health:heartbeat` artisan command for scheduler monitoring
- Graceful degradation when cache backend is unavailable
- Graceful handling of unresolvable or invalid check classes
- Duplicate check name deduplication in JSON output
- Full documentation with probe design guidance
- 113 tests with PHPStan level 9 static analysis
