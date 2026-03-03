---
title: Installation
description: Install and configure Laravel Health in your application.
weight: 2
---

# Installation

## Requirements

- PHP 8.3+
- Laravel 11.x or 12.x

## Install via Composer

```bash
composer require cboxdk/laravel-health
```

The package auto-discovers its service provider. No manual registration is needed.

## Publish Configuration

```bash
php artisan vendor:publish --tag="health-config"
```

This creates `config/health.php` with all default values.

## Verify Installation

```bash
curl http://localhost/health
```

A `200` response with JSON output confirms the package is working. The default liveness endpoint runs a database check.

## Related Documentation

- [Quick Start](quickstart.md)
- [Configuration](configuration.md)
- [Health Checks](health-checks/_index.md)
