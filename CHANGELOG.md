# Changelog

All notable changes to `laravel-health` will be documented in this file.

## 3.0.0 - 2026-09-26

Breaking release; see [UPGRADING.md](UPGRADING.md).

### Added
- `Contracts\RunsHealthChecks`: the runner contract every endpoint, the dashboard and `health:check` resolve. Rebind it to decorate or replace the runner.
- `Testing\FakeHealthCheckRunner` and the `Testing\InteractsWithHealth` trait (`fakeHealth()`, `authorizeHealthEndpoints()`, `resetHealthAuthorization()`) for testing health endpoints without running real checks.
- `HealthReport::fromResults()` to build a report from check results (worst status wins).
- `Exceptions\HealthException` marker interface on every package exception, and `Exceptions\InvalidConfigurationException`.
- Supply-chain gates: `composer license-check`, a committed CycloneDX SBOM (`composer sbom`), `composer audit`, and `composer qa` to run the whole gate. CI runs them in a new `security` workflow; Dependabot keeps actions and dependencies current.

### Changed
- **Breaking:** Require PHP `^8.4` and Laravel `^12.0 || ^13.0`. Laravel 11 is end-of-life and every 11.x release carries open security advisories, so Composer refuses to install it. Projects on Laravel 11 or PHP 8.3 stay on `2.0.x`.
- **Breaking:** Config values are read through typed accessors. A value of the wrong type throws `InvalidConfigurationException` naming the key instead of being silently cast. Numeric and boolean strings from `env()` are still accepted.
- **Breaking:** `health.metrics.prometheus.namespace` must be a valid Prometheus prefix; an invalid one (e.g. `my-app`) used to produce unscrapable metric names and now throws.
- **Breaking:** `health.metrics.prometheus.enabled` (`HEALTH_PROMETHEUS_ENABLED`) now disables the Prometheus endpoint. It was documented but ignored.
- **Breaking:** Removed the unused `health.auth` container binding.
- Dropped the `spatie/laravel-package-tools` dependency; the service provider extends Laravel's `ServiceProvider`. Publish tags are unchanged.
- The dashboard no longer loads Tailwind from a CDN. It has no external assets, so it works offline and under a strict script policy.
- Routes are registered from a single endpoint table; paths, names and defaults are unchanged.

### Fixed
- `MemoryCheck` is container-aware: inside a cgroup-limited container it measures usage against the container's memory limit instead of host RAM, matching the JSON and Prometheus memory figures. Its metadata gains a `source` key.
- `health:check --endpoint=status` is rejected; only the probe endpoints (`liveness`, `readiness`, `startup`) have checks.
- An array `?token[]=` query parameter no longer causes a 500 in `EndpointAuth`; it is ignored and the bearer token is used instead.
- `ScheduleCheck` reports a clear message when the heartbeat cache value is not a timestamp.
- `HEALTH_ALLOWED_IPS=true` no longer breaks config loading.

### Internal
- PHPStan runs at level `max` over `src`, `config`, `routes` and `tests/Fixtures` with no baseline and no inline `@var` overrides.
- CI matrix: PHP 8.4/8.5 × Laravel 12/13, lowest and stable dependencies.
- Architecture tests enforce strict types, final-by-default (with the reason for every open class) and that consumers depend on the runner contract.

## 2.0.0 - 2026-04-30

### Changed
- **Breaking:** Bump `cboxdk/system-metrics` dependency from `^2.1` to `^3.0`. CPU core values (`core_count`, `host_cpu_cores`, `cores`) are now `float` instead of `int`, supporting fractional CPU allocations in containerized environments (e.g. 0.5 cores).

## 1.0.1 - 2026-03-20

### Fixed
- Use `environment->containerization->insideContainer` for the `containerized` flag in system metrics instead of `SystemLimits::isContainerized()`. The cgroup source alone is unreliable on modern Linux where cgroup v2 is default even on VMs.

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
