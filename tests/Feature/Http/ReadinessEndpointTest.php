<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Contracts\HealthCheck;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\LaravelHealth;

beforeEach(function (): void {
    config()->set('health.security.public_endpoints', ['liveness']);
    config()->set('health.security.token', null);
    config()->set('health.security.allowed_ips', null);
    config()->set('health.cache.enabled', false);
    config()->set('health.checks.readiness', []);
    LaravelHealth::$authUsing = fn () => true;
});

afterEach(function (): void {
    LaravelHealth::$authUsing = null;
});

it('returns 200 when all checks pass', function (): void {
    $response = $this->getJson('/health/ready');

    $response->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('type', 'readiness');
});

it('returns 503 when checks fail', function (): void {
    $failingCheck = new class implements HealthCheck
    {
        public function name(): string
        {
            return 'failing';
        }

        public function run(): CheckResult
        {
            return CheckResult::critical('failing', 'Service down');
        }
    };

    config()->set('health.checks.readiness', [$failingCheck::class]);
    app()->bind($failingCheck::class, fn () => $failingCheck);

    $response = $this->getJson('/health/ready');

    $response->assertStatus(503)
        ->assertJsonPath('status', 'critical');
});
