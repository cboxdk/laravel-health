---
title: Cache Check
description: Verify cache read/write functionality.
weight: 12
---

# Cache Check

Writes a test value to cache, reads it back, and deletes it.

## Configuration

```php
'checks_config' => [
    'cache' => ['store' => null], // null = default cache store
],
```

## Usage

```php
use Cbox\LaravelHealth\Checks\CacheCheck;

'checks' => [
    'readiness' => [
        CacheCheck::class,
    ],
],
```

## Behavior

- Writes a test key with a known value
- Reads it back and verifies the value matches
- Deletes the test key
- Returns `ok` on success, `critical` on any failure

## Related Documentation

- [Health Checks Overview](_index.md)
- [Configuration](../configuration.md)
