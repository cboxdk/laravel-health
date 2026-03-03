---
title: Redis Check
description: Verify Redis connectivity.
weight: 15
---

# Redis Check

Sends a `PING` command to Redis and validates the response.

## Configuration

```php
'checks_config' => [
    'redis' => ['connection' => 'default'],
],
```

## Usage

```php
use Cbox\LaravelHealth\Checks\RedisCheck;

'checks' => [
    'readiness' => [
        RedisCheck::class,
    ],
],
```

## Behavior

- Sends `PING` to the configured Redis connection
- Accepts `true`, `PONG`, or `+PONG` as valid responses
- Returns `ok` on valid response, `critical` on failure

## Related Documentation

- [Health Checks Overview](_index.md)
- [Configuration](../configuration.md)
