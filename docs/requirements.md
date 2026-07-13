---
title: Requirements
description: Runtime requirements enforced by the package's composer constraints.
weight: 4
---

# Requirements

Health for Laravel installs only when your project satisfies the constraints declared
in the package's `composer.json`. These are the versions the dependency resolver
enforces — nothing more.

## PHP

- **PHP `^8.3`** — PHP 8.3 or newer.

## Laravel

- **`illuminate/contracts` `^11.0 | ^12.0 | ^13.0`** — Laravel 11, 12, or 13.

## Direct dependencies

- **`cboxdk/system-metrics` `^3.0`** — system metrics collection (CPU, memory, disk,
  network) and container-aware cgroup detection.
- **`spatie/laravel-package-tools` `^1.16`** — package service-provider scaffolding.
