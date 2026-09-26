---
title: Requirements
description: Runtime requirements enforced by the package's composer constraints.
weight: 3
---

# Requirements

Health for Laravel installs only when your project satisfies the constraints declared
in the package's `composer.json`. These are the versions the dependency resolver
enforces — nothing more.

## PHP

- **PHP `^8.4`** — PHP 8.4 or newer.

## Laravel

- **Laravel 12 or 13** — the package requires the `illuminate/*` components below at
  `^12.0 || ^13.0`.

## Direct dependencies

- **`cboxdk/system-metrics` `^3.0`** — system metrics collection (CPU, memory, disk,
  network) and container-aware cgroup detection.
- **`illuminate/console`, `illuminate/contracts`, `illuminate/http`,
  `illuminate/routing`, `illuminate/support`, `illuminate/view`** `^12.0 || ^13.0` —
  the Laravel components the package uses.

The package has no other runtime dependencies. Projects on Laravel 11 or PHP 8.3 stay
on the `2.0.x` line; see the [upgrade guide](../UPGRADING.md).

## Related Documentation

- [Installation](getting-started/installation.md)
- [Changelog](../CHANGELOG.md)
