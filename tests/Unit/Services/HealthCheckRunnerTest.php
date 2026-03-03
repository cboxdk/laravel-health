<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Contracts\HealthCheck;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Enums\Status;
use Cbox\LaravelHealth\Services\HealthCheckRunner;

// Create fake check classes for testing
beforeEach(function (): void {
    config()->set('health.cache.enabled', false);
});

it('runs checks and returns report', function (): void {
    $fakeCheck = new class implements HealthCheck
    {
        public function name(): string
        {
            return 'fake';
        }

        public function run(): CheckResult
        {
            return CheckResult::ok('fake');
        }
    };

    config()->set('health.checks.liveness', [$fakeCheck::class]);

    app()->bind($fakeCheck::class, fn () => $fakeCheck);

    $runner = app(HealthCheckRunner::class);
    $report = $runner->run(EndpointType::Liveness);

    expect($report->status)->toBe(Status::Ok)
        ->and($report->results)->toHaveCount(1)
        ->and($report->results[0]->name)->toBe('fake')
        ->and($report->results[0]->durationMs)->toBeGreaterThan(0);
});

it('catches exceptions and returns critical', function (): void {
    $failingCheck = new class implements HealthCheck
    {
        public function name(): string
        {
            return 'failing';
        }

        public function run(): CheckResult
        {
            throw new RuntimeException('Connection refused');
        }
    };

    config()->set('health.checks.liveness', [$failingCheck::class]);

    app()->bind($failingCheck::class, fn () => $failingCheck);

    $runner = app(HealthCheckRunner::class);
    $report = $runner->run(EndpointType::Liveness);

    expect($report->status)->toBe(Status::Critical)
        ->and($report->results[0]->message)->toBe('Connection refused');
});

it('returns ok for empty check list', function (): void {
    config()->set('health.checks.startup', []);

    $runner = app(HealthCheckRunner::class);
    $report = $runner->run(EndpointType::Startup);

    expect($report->status)->toBe(Status::Ok)
        ->and($report->results)->toBeEmpty();
});

it('determines worst status across checks', function (): void {
    $okCheck = new class implements HealthCheck
    {
        public function name(): string
        {
            return 'ok_check';
        }

        public function run(): CheckResult
        {
            return CheckResult::ok('ok_check');
        }
    };

    $criticalCheck = new class implements HealthCheck
    {
        public function name(): string
        {
            return 'critical_check';
        }

        public function run(): CheckResult
        {
            return CheckResult::critical('critical_check', 'Failed');
        }
    };

    config()->set('health.checks.readiness', [$okCheck::class, $criticalCheck::class]);

    app()->bind($okCheck::class, fn () => $okCheck);
    app()->bind($criticalCheck::class, fn () => $criticalCheck);

    $runner = app(HealthCheckRunner::class);
    $report = $runner->run(EndpointType::Readiness);

    expect($report->status)->toBe(Status::Critical)
        ->and($report->results)->toHaveCount(2);
});
