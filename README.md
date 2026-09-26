# Health for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cboxdk/laravel-health.svg?style=flat-square)](https://packagist.org/packages/cboxdk/laravel-health)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cboxdk/laravel-health/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cboxdk/laravel-health/actions?query=workflow%3Arun-tests+branch%3Amain)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/cboxdk/laravel-health/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/cboxdk/laravel-health/actions?query=workflow%3Aphpstan+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cboxdk/laravel-health.svg?style=flat-square)](https://packagist.org/packages/cboxdk/laravel-health)
![PHP Version](https://img.shields.io/packagist/php-v/cboxdk/laravel-health?style=flat-square)
![Laravel Version](https://img.shields.io/badge/laravel-12.x%20|%2013.x-blue?style=flat-square)

Health checks, Kubernetes probes, Prometheus metrics, and system monitoring for Laravel.

## Quick Start

```bash
composer require cboxdk/laravel-health
php artisan vendor:publish --tag="health-config"
curl http://localhost/health
```

## Features

- **Kubernetes Probes** — liveness, readiness, and startup endpoints out of the box
- **Prometheus Metrics** — `/health/metrics` with health check status and system metrics
- **10 Built-in Checks** — database, cache, queue, storage, Redis, environment, schedule, CPU, memory, disk space
- **System Metrics** — CPU load, memory, disk, network via [cboxdk/system-metrics](https://github.com/cboxdk/system-metrics)
- **Container Aware** — automatic cgroup detection for Docker/Kubernetes
- **JSON Metrics API** — structured system metrics at `/health/metrics/json`
- **HTML Dashboard** — optional real-time status UI
- **Token & IP Auth** — protect endpoints with bearer tokens and IP allowlists
- **Response Caching** — configurable TTL to reduce check overhead
- **Fully Extensible** — implement the `HealthCheck` contract to add checks, or rebind `RunsHealthChecks` to replace the runner
- **Test Helpers** — `InteractsWithHealth` and `FakeHealthCheckRunner` to test your endpoints without real checks
- **No External Assets** — the dashboard ships its own CSS; nothing is loaded from a CDN

## Requirements

- PHP 8.4+
- Laravel 12.x or 13.x

## Documentation

Full documentation is available in the [docs/](docs/index.md) directory. Upgrading from 2.x? Read [UPGRADING.md](UPGRADING.md).

## Development

```bash
composer qa        # code style, PHPStan (level max), tests, license check, audit
composer test
composer analyse
composer format
```

## Security

Please report vulnerabilities privately; see [SECURITY.md](SECURITY.md).

## Credits

- [Sylvester Damgaard](https://github.com/cboxdk)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
