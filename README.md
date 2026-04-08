# Health for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cboxdk/laravel-health.svg?style=flat-square)](https://packagist.org/packages/cboxdk/laravel-health)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cboxdk/laravel-health/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cboxdk/laravel-health/actions?query=workflow%3Arun-tests+branch%3Amain)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/cboxdk/laravel-health/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/cboxdk/laravel-health/actions?query=workflow%3Aphpstan+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cboxdk/laravel-health.svg?style=flat-square)](https://packagist.org/packages/cboxdk/laravel-health)
![PHP Version](https://img.shields.io/packagist/php-v/cboxdk/laravel-health?style=flat-square)
![Laravel Version](https://img.shields.io/badge/laravel-11.x%20|%2012.x%20|%2013.x-blue?style=flat-square)

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
- **Fully Extensible** — implement `HealthCheck` contract to add custom checks

## Requirements

- PHP 8.3+
- Laravel 11.x or 12.x

## Documentation

Full documentation is available in the [docs/](docs/introduction.md) directory.

## Testing

```bash
composer test
composer analyse
composer format
```

## Credits

- [Sylvester Damgaard](https://github.com/cboxdk)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
