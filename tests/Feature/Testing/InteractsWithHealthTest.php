<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\LaravelHealth;
use Cbox\LaravelHealth\Testing\FakeHealthCheckRunner;

beforeEach(function (): void {
    config()->set('health.security.token', null);
    config()->set('health.security.allowed_ips', null);
});

afterEach(function (): void {
    $this->resetHealthAuthorization();
});

it('swaps the runner contract for the fake', function (): void {
    $fake = $this->fakeHealth();

    expect(app(RunsHealthChecks::class))->toBe($fake)->toBeInstanceOf(FakeHealthCheckRunner::class);
});

it('drives the probe endpoints from the fake', function (): void {
    $this->authorizeHealthEndpoints();

    $fake = $this->fakeHealth()
        ->returns(EndpointType::Readiness, CheckResult::critical('database', 'Connection refused'));

    $this->getJson('/health')->assertOk()->assertJsonPath('status', 'ok');

    $this->getJson('/health/ready')
        ->assertServiceUnavailable()
        ->assertJsonPath('status', 'critical')
        ->assertJsonPath('checks.database.message', 'Connection refused');

    $fake->assertRan(EndpointType::Liveness, 1);
    $fake->assertRan(EndpointType::Readiness, 1);
    $fake->assertNotRan(EndpointType::Startup);
});

it('reports a warning as passing', function (): void {
    $this->authorizeHealthEndpoints();

    $this->fakeHealth()->returns(EndpointType::Startup, CheckResult::warning('schedule', 'No heartbeat yet'));

    $this->getJson('/health/startup')->assertOk()->assertJsonPath('status', 'warning');
});

it('drives the health:check command from the fake', function (): void {
    $this->fakeHealth()->returns(EndpointType::Readiness, CheckResult::critical('queue'));

    $this->artisan('health:check', ['--endpoint' => 'liveness'])->assertSuccessful();
    $this->artisan('health:check', ['--endpoint' => 'readiness'])->assertFailed();
});

it('denies endpoint access when authorization is revoked', function (): void {
    app()->detectEnvironment(fn () => 'local');
    $this->authorizeHealthEndpoints(false);
    $this->fakeHealth();

    $this->getJson('/health/ready')->assertForbidden();
});

it('resets the auth callback', function (): void {
    $this->authorizeHealthEndpoints();
    $this->resetHealthAuthorization();

    expect(LaravelHealth::$authUsing)->toBeNull();
});
