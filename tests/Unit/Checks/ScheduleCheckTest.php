<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\ScheduleCheck;
use Cbox\LaravelHealth\Enums\Status;
use Illuminate\Support\Facades\Cache;

it('returns warning when no heartbeat found', function (): void {
    Cache::forget('health:schedule:heartbeat');

    $check = new ScheduleCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Warning)
        ->and($result->message)->toContain('No scheduler heartbeat found');
});

it('passes when heartbeat is recent', function (): void {
    Cache::put('health:schedule:heartbeat', now());

    $check = new ScheduleCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok);
});

it('fails when heartbeat is stale', function (): void {
    config()->set('health.checks_config.schedule.max_age_minutes', 5);
    Cache::put('health:schedule:heartbeat', now()->subMinutes(10));

    $check = new ScheduleCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Critical)
        ->and($result->message)->toContain('minutes old');
});
