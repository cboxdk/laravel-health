---
title: Environment Check
description: Verify required environment variables are set.
weight: 46
---

# Environment Check

Verifies that all required environment variables are present.

## Configuration

```php
'checks_config' => [
    'environment' => [
        'required' => ['APP_KEY', 'DB_HOST', 'REDIS_HOST'],
    ],
],
```

## Usage

```php
use Cbox\LaravelHealth\Checks\EnvironmentCheck;

'checks' => [
    'startup' => [
        EnvironmentCheck::class,
    ],
],
```

## Behavior

- Checks each variable in the `required` array with PHP's `getenv()`; a variable set to an empty string counts as present
- Returns `ok` when all required variables exist
- Returns `critical` with `missing` metadata listing absent variables

When the configuration is cached (`php artisan config:cache`), Laravel doesn't load `.env`, so variables defined only in `.env` are not visible to `getenv()`. Set required variables in the real process environment (container env, systemd unit, PHP-FPM pool) when you cache config.

## Related Documentation

- [Health Checks Overview](_index.md)
- [Configuration Reference](../configuration/reference.md)
