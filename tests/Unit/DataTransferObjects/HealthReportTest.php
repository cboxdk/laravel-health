<?php

declare(strict_types=1);

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Enums\Status;

it('creates a health report', function (): void {
    $results = [
        CheckResult::ok('database')->withDuration(1.5),
        CheckResult::ok('cache')->withDuration(0.5),
    ];

    $report = new HealthReport(
        type: EndpointType::Readiness,
        status: Status::Ok,
        results: $results,
        totalDurationMs: 2.0,
        checkedAt: new DateTimeImmutable('2024-01-01 12:00:00'),
    );

    expect($report->type)->toBe(EndpointType::Readiness)
        ->and($report->status)->toBe(Status::Ok)
        ->and($report->results)->toHaveCount(2)
        ->and($report->totalDurationMs)->toBe(2.0);
});

it('determines passing correctly', function (): void {
    $passing = new HealthReport(
        type: EndpointType::Liveness,
        status: Status::Ok,
        results: [],
        totalDurationMs: 0.0,
        checkedAt: new DateTimeImmutable,
    );

    $failing = new HealthReport(
        type: EndpointType::Liveness,
        status: Status::Critical,
        results: [],
        totalDurationMs: 0.0,
        checkedAt: new DateTimeImmutable,
    );

    expect($passing->isPassing())->toBeTrue()
        ->and($failing->isPassing())->toBeFalse();
});

it('converts to array', function (): void {
    $results = [
        CheckResult::ok('database', 'OK')->withDuration(1.5),
    ];

    $report = new HealthReport(
        type: EndpointType::Liveness,
        status: Status::Ok,
        results: $results,
        totalDurationMs: 1.5,
        checkedAt: new DateTimeImmutable('2024-01-01 12:00:00'),
    );

    $array = $report->toArray();

    expect($array)->toHaveKeys(['status', 'type', 'checks', 'total_duration_ms', 'checked_at'])
        ->and($array['status'])->toBe('ok')
        ->and($array['type'])->toBe('liveness')
        ->and($array['checks'])->toHaveKey('database')
        ->and($array['checks']['database']['status'])->toBe('ok');
});

it('handles duplicate check names by appending a suffix', function (): void {
    $results = [
        CheckResult::ok('database', 'Primary OK')->withDuration(1.0),
        CheckResult::ok('database', 'Replica OK')->withDuration(2.0),
        CheckResult::ok('database', 'Analytics OK')->withDuration(3.0),
    ];

    $report = new HealthReport(
        type: EndpointType::Readiness,
        status: Status::Ok,
        results: $results,
        totalDurationMs: 6.0,
        checkedAt: new DateTimeImmutable('2024-01-01 12:00:00'),
    );

    $array = $report->toArray();

    expect($array['checks'])->toHaveCount(3)
        ->toHaveKeys(['database', 'database_2', 'database_3'])
        ->and($array['checks']['database']['message'])->toBe('Primary OK')
        ->and($array['checks']['database_2']['message'])->toBe('Replica OK')
        ->and($array['checks']['database_3']['message'])->toBe('Analytics OK');
});
