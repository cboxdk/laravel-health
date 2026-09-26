# Agent guide — cboxdk/laravel-health

Health checks, Kubernetes probes, Prometheus metrics and system metrics for Laravel.
Docs start at `docs/index.md`; the mental model is `docs/core-concepts/architecture.md`.

## Commands

```bash
composer qa             # the pre-commit gate: pint --test, phpstan (max), pest, license check, audit
composer test           # pest only
composer analyse        # phpstan level max over src, config, routes, tests/Fixtures
composer format         # pint (fix)
composer sbom           # regenerate sbom.json after any dependency change — CI fails on drift
```

## Architecture map

- `src/Contracts/RunsHealthChecks.php` — what every endpoint, the dashboard and `health:check`
  resolve. `Services/HealthCheckRunner` implements it (cache + per-check timing + isolation).
- `src/Contracts/HealthCheck.php` — one check; `Checks/BaseCheck` derives the name.
- `src/DataTransferObjects/` — `CheckResult`, `HealthReport` (readonly; `fromResults()` = worst status).
- `src/Config/TypedConfig.php` — the only way config is read. `HealthConfig` holds the
  resolved security settings.
- `src/Http/` — thin controllers, `EndpointAuth` + `AllowIps` middleware.
- `routes/health.php` — table-driven endpoint registration from config.
- `src/Services/PrometheusRenderer.php`, `SystemMetricsService.php` — exposition formats over
  `cboxdk/system-metrics`.
- `src/Testing/` — `FakeHealthCheckRunner` and the `InteractsWithHealth` trait; the package's
  own `tests/TestCase.php` uses them.

## Invariants — do not break these

1. **A check never takes an endpoint down.** The runner catches every `Throwable` from a check
   and turns it into a critical `CheckResult`. Only configuration errors may throw.
2. **Deny by default.** Non-public endpoints require a valid token or a passing auth callback;
   the default callback allows only the `local` environment. Invalid allowlist entries are
   dropped, never trusted.
3. **No `mixed` leaks.** Read config through `TypedConfig`; narrow other `mixed` values with
   type checks. No baseline, no `@phpstan-ignore`, no inline `@var` overrides, no casts to
   silence PHPStan.
4. **Depend on `RunsHealthChecks`, never on `HealthCheckRunner`** (enforced in `tests/ArchTest.php`).
5. **The dashboard loads nothing external** — no CDN scripts, styles or fonts.
6. **Final by default.** Every open class is listed in `tests/ArchTest.php` with the reason.

## Conventions

- PHP ^8.4, Laravel 12 and 13, `declare(strict_types=1)` everywhere, Pest, Larastan level max.
- New behaviour ships with tests; public API changes ship with docs, a `CHANGELOG.md` entry
  and, when breaking, an `UPGRADING.md` entry.
- Docs follow the Cbox topic-folder layout: only `index.md`, `quickstart.md` and
  `requirements.md` at the root, an `_index.md` in every folder, `title`/`weight`/`description`
  frontmatter on every file.
