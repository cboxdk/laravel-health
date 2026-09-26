<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\MemoryCheck;
use Cbox\LaravelHealth\Enums\Status;
use Cbox\SystemMetrics\DTO\Metrics\LimitSource;
use Cbox\SystemMetrics\DTO\Metrics\SystemLimits;
use Cbox\SystemMetrics\Exceptions\SystemMetricsException;
use Cbox\SystemMetrics\Testing\FakeSystemMetrics;

it('passes when memory usage is below threshold', function (): void {
    config()->set('health.thresholds.memory_percent', 99.9);

    $check = new MemoryCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok)
        ->and($result->name)->toBe('memory')
        ->and($result->metadata)->toHaveKeys(['used_percent', 'used_bytes', 'total_bytes', 'threshold', 'source']);
});

it('returns critical when memory exceeds threshold', function (): void {
    config()->set('health.thresholds.memory_percent', 0.001);

    $check = new MemoryCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Critical)
        ->and($result->message)->toContain('exceeds threshold')
        ->and($result->metadata)->toHaveKeys(['used_percent', 'used_bytes', 'total_bytes', 'threshold', 'source']);
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

describe('with faked system metrics', function (): void {
    afterEach(fn () => FakeSystemMetrics::uninstall());

    it('measures against the container memory limit, not host RAM', function (): void {
        $fakes = FakeSystemMetrics::install();

        // 1.8 GB used of a 2 GB container limit, on a host whose own RAM is mostly free.
        $fakes->limits->set(new SystemLimits(
            source: LimitSource::CGROUP_V2,
            cpuCores: 2.0,
            memoryBytes: 2_000_000_000,
            currentCpuCores: 0.5,
            currentMemoryBytes: 1_800_000_000.0,
        ));

        config()->set('health.thresholds.memory_percent', 85);

        $result = (new MemoryCheck)->run();

        expect($result->status)->toBe(Status::Critical)
            ->and($result->metadata['used_percent'])->toBe(90.0)
            ->and($result->metadata['total_bytes'])->toBe(2_000_000_000)
            ->and($result->metadata['source'])->toBe('cgroup_v2');
    });

    it('falls back to host memory when limits cannot be read', function (): void {
        $fakes = FakeSystemMetrics::install();
        $fakes->limits->failWith(new SystemMetricsException('no cgroup'));

        $result = (new MemoryCheck)->run();

        expect($result->status)->toBe(Status::Ok)
            ->and($result->metadata['source'])->toBe('host');
    });

    it('is unknown when no memory metrics can be read', function (): void {
        $fakes = FakeSystemMetrics::install();
        $fakes->limits->failWith(new SystemMetricsException('no cgroup'));
        $fakes->memory->failWith(new SystemMetricsException('no meminfo'));

        expect((new MemoryCheck)->run()->status)->toBe(Status::Unknown);
    });
});
