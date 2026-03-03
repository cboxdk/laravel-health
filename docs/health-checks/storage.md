---
title: Storage Check
description: Verify filesystem read/write permissions.
weight: 14
---

# Storage Check

Creates and deletes a test file on the configured disk to verify read/write permissions.

## Configuration

```php
'checks_config' => [
    'storage' => ['disk' => 'local'],
],
```

## Usage

```php
use Cbox\LaravelHealth\Checks\StorageCheck;

'checks' => [
    'readiness' => [
        StorageCheck::class,
    ],
],
```

## Behavior

- Writes a test file to the configured disk
- Deletes the test file
- Returns `ok` on success, `critical` on any failure

## Related Documentation

- [Health Checks Overview](_index.md)
- [Configuration](../configuration.md)
