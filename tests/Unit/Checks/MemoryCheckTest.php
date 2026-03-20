<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\MemoryCheck;
use Cbox\LaravelHealth\Enums\Status;

it('passes when memory usage is below threshold', function (): void {
    config()->set('health.thresholds.memory_percent', 99.9);

    $check = new MemoryCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok)
        ->and($result->name)->toBe('memory')
        ->and($result->metadata)->toHaveKeys(['used_percent', 'used_bytes', 'total_bytes', 'threshold']);
});

it('returns critical when memory exceeds threshold', function (): void {
    config()->set('health.thresholds.memory_percent', 0.001);

    $check = new MemoryCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Critical)
        ->and($result->message)->toContain('exceeds threshold')
        ->and($result->metadata)->toHaveKeys(['used_percent', 'used_bytes', 'total_bytes', 'threshold']);
});

it('includes threshold in metadata', function (): void {
    config()->set('health.thresholds.memory_percent', 85);

    $check = new MemoryCheck;
    $result = $check->run();

    expect($result->metadata['threshold'])->toBe(85);
});

it('derives name correctly', function (): void {
    $check = new MemoryCheck;

    expect($check->name())->toBe('memory');
});
