<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\CpuCheck;
use Cbox\LaravelHealth\Enums\Status;

it('passes when cpu load is below threshold', function (): void {
    config()->set('health.thresholds.cpu_load_per_core', 999.0);

    $check = new CpuCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok)
        ->and($result->name)->toBe('cpu')
        ->and($result->metadata)->toHaveKeys([
            'load_1m', 'load_5m', 'load_15m', 'cores', 'normalized_1m', 'threshold_per_core',
        ]);
});

it('returns critical when cpu load exceeds threshold', function (): void {
    config()->set('health.thresholds.cpu_load_per_core', 0.0001);

    $check = new CpuCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Critical)
        ->and($result->message)->toContain('Load per core')
        ->and($result->message)->toContain('exceeds threshold');
});

it('includes core count in metadata', function (): void {
    config()->set('health.thresholds.cpu_load_per_core', 999.0);

    $check = new CpuCheck;
    $result = $check->run();

    expect($result->metadata['cores'])->toBeGreaterThan(0);
});

it('includes threshold in metadata', function (): void {
    config()->set('health.thresholds.cpu_load_per_core', 3.5);

    $check = new CpuCheck;
    $result = $check->run();

    expect($result->metadata['threshold_per_core'])->toBe(3.5);
});

it('derives name correctly', function (): void {
    $check = new CpuCheck;

    expect($check->name())->toBe('cpu');
});
