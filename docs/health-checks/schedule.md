---
title: Schedule Check
description: Verify the task scheduler is running.
weight: 17
---

# Schedule Check

Checks for a heartbeat timestamp in cache to verify the scheduler is active.

## Configuration

```php
'checks_config' => [
    'schedule' => ['max_age_minutes' => 5],
],
```

## Setup

Add a heartbeat command to your scheduler in `app/Console/Kernel.php` or `routes/console.php`:

```php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Cache::put('health:schedule:heartbeat', now()->timestamp, 600);
})->everyMinute();
```

## Usage

```php
use Cbox\LaravelHealth\Checks\ScheduleCheck;

'checks' => [
    'readiness' => [
        ScheduleCheck::class,
    ],
],
```

## Behavior

- Reads the heartbeat timestamp from cache
- Returns `warning` if no heartbeat is found
- Returns `critical` if the heartbeat is older than `max_age_minutes`
- Returns `ok` when the heartbeat is fresh
- Includes `age_minutes` and `max_age_minutes` in metadata

## Related Documentation

- [Health Checks Overview](_index.md)
- [Configuration](../configuration.md)
