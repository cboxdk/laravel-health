<?php

declare(strict_types=1);

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\Enums\Status;

it('creates ok result', function (): void {
    $result = CheckResult::ok('database', 'Connection successful');

    expect($result->name)->toBe('database')
        ->and($result->status)->toBe(Status::Ok)
        ->and($result->message)->toBe('Connection successful')
        ->and($result->durationMs)->toBe(0.0)
        ->and($result->metadata)->toBe([]);
});

it('creates warning result', function (): void {
    $result = CheckResult::warning('memory', 'High usage');

    expect($result->status)->toBe(Status::Warning)
        ->and($result->message)->toBe('High usage');
});

it('creates critical result', function (): void {
    $result = CheckResult::critical('disk', 'Full', ['used_percent' => 99.5]);

    expect($result->status)->toBe(Status::Critical)
        ->and($result->metadata)->toBe(['used_percent' => 99.5]);
});

it('creates unknown result', function (): void {
    $result = CheckResult::unknown('cpu', 'Unable to read');

    expect($result->status)->toBe(Status::Unknown);
});

it('creates result with duration', function (): void {
    $result = CheckResult::ok('database')->withDuration(12.34);

    expect($result->durationMs)->toBe(12.34)
        ->and($result->name)->toBe('database')
        ->and($result->status)->toBe(Status::Ok);
});

it('converts to array', function (): void {
    $result = CheckResult::ok('database', 'OK', ['connection' => 'mysql'])
        ->withDuration(5.678);

    $array = $result->toArray();

    expect($array)->toHaveKeys(['name', 'status', 'message', 'duration_ms', 'metadata'])
        ->and($array['name'])->toBe('database')
        ->and($array['status'])->toBe('ok')
        ->and($array['duration_ms'])->toBe(5.68)
        ->and($array['metadata'])->toBe(['connection' => 'mysql']);
});
