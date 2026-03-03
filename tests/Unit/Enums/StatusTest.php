<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Enums\Status;

it('has correct values', function (): void {
    expect(Status::Ok->value)->toBe('ok')
        ->and(Status::Warning->value)->toBe('warning')
        ->and(Status::Critical->value)->toBe('critical')
        ->and(Status::Unknown->value)->toBe('unknown');
});

it('determines healthy status correctly', function (): void {
    expect(Status::Ok->isHealthy())->toBeTrue()
        ->and(Status::Warning->isHealthy())->toBeTrue()
        ->and(Status::Critical->isHealthy())->toBeFalse()
        ->and(Status::Unknown->isHealthy())->toBeFalse();
});

it('determines passing status correctly', function (): void {
    expect(Status::Ok->isPassing())->toBeTrue()
        ->and(Status::Warning->isPassing())->toBeFalse()
        ->and(Status::Critical->isPassing())->toBeFalse()
        ->and(Status::Unknown->isPassing())->toBeFalse();
});

it('returns worst status from list', function (): void {
    expect(Status::worst([Status::Ok, Status::Ok]))->toBe(Status::Ok)
        ->and(Status::worst([Status::Ok, Status::Warning]))->toBe(Status::Warning)
        ->and(Status::worst([Status::Ok, Status::Critical]))->toBe(Status::Critical)
        ->and(Status::worst([Status::Warning, Status::Unknown]))->toBe(Status::Unknown)
        ->and(Status::worst([Status::Ok, Status::Warning, Status::Critical]))->toBe(Status::Critical);
});

it('returns ok for empty list', function (): void {
    expect(Status::worst([]))->toBe(Status::Ok);
});

it('returns numeric values', function (): void {
    expect(Status::Ok->numericValue())->toBe(1.0)
        ->and(Status::Warning->numericValue())->toBe(0.5)
        ->and(Status::Critical->numericValue())->toBe(0.0)
        ->and(Status::Unknown->numericValue())->toBe(0.0);
});
