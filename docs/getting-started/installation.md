---
title: Installation
description: Install and configure Health for Laravel in your application.
weight: 11
---

# Installation

## Requirements

- PHP 8.4+
- Laravel 12.x or 13.x

See [Requirements](../requirements.md) for the exact Composer constraints.

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

## Publish Views (optional)

To customize the [dashboard](../endpoints/dashboard.md) template:

```bash
php artisan vendor:publish --tag="health-views"
```

The views are copied to `resources/views/vendor/health`.

## Verify Installation

```bash
curl http://localhost/health
```

A `200` response with JSON output confirms the package is working. The default liveness endpoint runs a database check and is public, so no token is needed. You can also run the checks from the terminal:

```bash
php artisan health:check
```

## Related Documentation

- [Quick Start](../quickstart.md)
- [Configuration Reference](../configuration/reference.md)
- [Health Checks](../health-checks/_index.md)
- [Upgrade guide](../../UPGRADING.md)
