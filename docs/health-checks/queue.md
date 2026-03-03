---
title: Queue Check
description: Report queue size.
weight: 13
---

# Queue Check

Retrieves the current queue size from the configured connection.

## Configuration

```php
'checks_config' => [
    'queue' => ['connection' => null], // null = default queue connection
],
```

## Usage

```php
use Cbox\LaravelHealth\Checks\QueueCheck;

'checks' => [
    'readiness' => [
        QueueCheck::class,
    ],
],
```

## Behavior

- Reads the queue size from the configured connection
- Returns `ok` with `queue_size` in metadata
- Returns `critical` on connection failure

## Related Documentation

- [Health Checks Overview](_index.md)
- [Configuration](../configuration.md)
