---
title: Database Check
description: Verify database connectivity.
weight: 11
---

# Database Check

Verifies database connectivity by obtaining a PDO connection.

## Configuration

```php
'checks_config' => [
    'database' => ['connection' => null], // null = default connection
],
```

## Usage

```php
use Cbox\LaravelHealth\Checks\DatabaseCheck;

'checks' => [
    'liveness' => [
        DatabaseCheck::class,
    ],
],
```

## Behavior

- Returns `ok` when `DB::connection()->getPdo()` succeeds
- Returns `critical` with the exception message on failure

## Related Documentation

- [Health Checks Overview](_index.md)
- [Configuration](../configuration.md)
