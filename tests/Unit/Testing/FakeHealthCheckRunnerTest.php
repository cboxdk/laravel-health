<?php

declare(strict_types=1);

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Enums\Status;
use Cbox\LaravelHealth\Testing\FakeHealthCheckRunner;
use PHPUnit\Framework\AssertionFailedError;

it('returns a healthy empty report by default', function (): void {
    $report = (new FakeHealthCheckRunner)->run(EndpointType::Readiness);

    expect($report->type)->toBe(EndpointType::Readiness)
        ->and($report->status)->toBe(Status::Ok)
        ->and($report->results)->toBe([]);
});

it('builds the report from the given results with the worst status', function (): void {
    $fake = (new FakeHealthCheckRunner)->returns(
        EndpointType::Readiness,
        CheckResult::ok('database'),
        CheckResult::critical('cache', 'down'),
    );

    $report = $fake->run(EndpointType::Readiness);

    expect($report->status)->toBe(Status::Critical)
        ->and($report->results)->toHaveCount(2)
        ->and($fake->run(EndpointType::Liveness)->status)->toBe(Status::Ok);
});

it('returns a prepared report as-is', function (): void {
    $report = HealthReport::fromResults(EndpointType::Startup, [CheckResult::warning('migrations')]);

    expect((new FakeHealthCheckRunner)->returnsReport($report)->run(EndpointType::Startup))->toBe($report);
});

it('records and asserts runs', function (): void {
    $fake = new FakeHealthCheckRunner;

    $fake->run(EndpointType::Liveness);
    $fake->run(EndpointType::Liveness);

    $fake->assertRan(EndpointType::Liveness);
    $fake->assertRan(EndpointType::Liveness, 2);
    $fake->assertNotRan(EndpointType::Readiness);

    expect($fake->runs())->toBe([EndpointType::Liveness, EndpointType::Liveness]);
});

it('fails the assertions when runs do not match', function (Closure $assertion): void {
    $fake = new FakeHealthCheckRunner;
    $fake->run(EndpointType::Readiness);

    expect(fn () => $assertion($fake))->toThrow(AssertionFailedError::class);
})->with([
    'never ran' => [fn (FakeHealthCheckRunner $fake) => $fake->assertRan(EndpointType::Liveness)],
    'wrong count' => [fn (FakeHealthCheckRunner $fake) => $fake->assertRan(EndpointType::Readiness, 2)],
    'ran unexpectedly' => [fn (FakeHealthCheckRunner $fake) => $fake->assertNotRan(EndpointType::Readiness)],
]);
