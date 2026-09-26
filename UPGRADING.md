# Upgrading

Breaking changes only, newest first. Everything else is in [`CHANGELOG.md`](CHANGELOG.md).

## From 2.x to 3.0

### PHP 8.4 and Laravel 12 or 13

3.0 requires PHP `^8.4` and Laravel `^12.0 || ^13.0`. Laravel 11 is end-of-life and every 11.x
release has open security advisories, so Composer refuses to install it. Stay on `2.0.x` until
you can upgrade PHP and Laravel.

### Configuration values are type-checked

Config is read through typed accessors. A value of the wrong type now throws
`Cbox\LaravelHealth\Exceptions\InvalidConfigurationException` naming the key, where 2.x cast it
silently. Strings from `env()` are still accepted where they parse cleanly (`"30"` for a TTL,
`"false"` for a flag).

Check your published `config/health.php` for:

- `cache.ttl` and `checks_config.schedule.max_age_minutes` — must be integers or integer strings.
- `thresholds.*` — must be numbers or numeric strings.
- `checks.*` — must be lists of class names.
- `metrics.prometheus.namespace` — must be a valid Prometheus prefix (letters, digits and
  underscores, not starting with a digit). `my-app` now fails; use `my_app`.

### `HEALTH_PROMETHEUS_ENABLED=false` now disables `/metrics`

The `metrics.prometheus.enabled` flag was documented but ignored in 2.x. It now removes the
Prometheus route. If you set it to `false` and still scrape `/health/metrics`, remove the flag.

### The `health.auth` container binding is gone

It was an unused alias. Resolve `Cbox\LaravelHealth\LaravelHealth` or use the `LaravelHealth`
facade instead.

### `spatie/laravel-package-tools` is no longer installed with this package

The service provider now extends Laravel's own `ServiceProvider`. Publish tags are unchanged
(`health-config`, `health-views`). If your application uses `spatie/laravel-package-tools`
itself, require it directly.

### Published dashboard views

The dashboard no longer loads Tailwind from a CDN; it ships its own inline CSS. If you published
the views (`--tag=health-views`), your copy still uses the old markup. Delete
`resources/views/vendor/health` and publish again to pick up the new one.

### Runner contract (only if you swap the runner)

Endpoints, the dashboard and `health:check` now resolve
`Cbox\LaravelHealth\Contracts\RunsHealthChecks` instead of `Services\HealthCheckRunner`. To
replace or decorate the runner, bind the contract. Type-hinting `HealthCheckRunner` in your own
code keeps working.
