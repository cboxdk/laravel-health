<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\DiskSpaceCheck;
use Cbox\LaravelHealth\Enums\Status;

it('passes when disk usage is below threshold', function (): void {
    config()->set('health.thresholds.disk_space_percent', 101);

    $check = new DiskSpaceCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok)
        ->and($result->name)->toBe('disk_space')
        ->and($result->metadata)->toHaveKey('threshold');
});

it('returns critical when disk usage exceeds threshold', function (): void {
    config()->set('health.thresholds.disk_space_percent', 0.001);

    $check = new DiskSpaceCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Critical)
        ->and($result->message)->toContain('Disk usage exceeds threshold')
        ->and($result->metadata)->toHaveKeys(['threshold', 'critical_mounts']);
});

it('lists critical mounts in metadata when threshold exceeded', function (): void {
    config()->set('health.thresholds.disk_space_percent', 0.001);

    $check = new DiskSpaceCheck;
    $result = $check->run();

    expect($result->metadata['critical_mounts'])->toBeArray()
        ->and($result->metadata['critical_mounts'])->not->toBeEmpty();
});

it('derives name correctly', function (): void {
    $check = new DiskSpaceCheck;

    expect($check->name())->toBe('disk_space');
});
