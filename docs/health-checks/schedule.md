---
title: Schedule Check
description: Verify the task scheduler is running.
weight: 47
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

Schedule the built-in heartbeat command in your `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('health:heartbeat')->everyMinute();
```

The command writes the current time to the default cache store under `health:schedule:heartbeat` for 10 minutes. The check reads the same key from the default store, so every server running the check must share that store with the server running the scheduler.

Or if you prefer a manual approach:

```php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Cache::put('health:schedule:heartbeat', now(), 600);
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

- Reads the heartbeat timestamp from the default cache store
- Returns `warning` if no heartbeat is found
- Returns `critical` if the cached value is not a timestamp (a `DateTimeInterface`, such as `now()`)
- Returns `critical` if the heartbeat is more than `max_age_minutes` old, with `age_minutes` and `max_age_minutes` in metadata
- Returns `ok` when the heartbeat is fresh, with `age_minutes` in metadata

## Related Documentation

- [Health Checks Overview](_index.md)
- [Configuration Reference](../configuration/reference.md)
