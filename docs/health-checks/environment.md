---
title: Environment Check
description: Verify required environment variables are set.
weight: 16
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

- Checks each variable in the `required` array
- Returns `ok` when all required variables exist
- Returns `critical` with `missing` metadata listing absent variables

## Related Documentation

- [Health Checks Overview](_index.md)
- [Configuration](../configuration.md)
