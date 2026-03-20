<?php

declare(strict_types=1);

use Cbox\LaravelHealth\LaravelHealth;

beforeEach(function (): void {
    config()->set('health.security.public_endpoints', ['liveness']);
    config()->set('health.security.token', null);
    config()->set('health.security.allowed_ips', null);
    config()->set('health.cache.enabled', false);
    config()->set('health.checks.startup', []);
    LaravelHealth::$authUsing = fn () => true;
});

afterEach(function (): void {
    LaravelHealth::$authUsing = null;
});

it('returns 200 when all startup checks pass', function (): void {
    $response = $this->getJson('/health/startup');

    $response->assertOk()
        ->assertJsonStructure(['status', 'type', 'checks', 'total_duration_ms', 'checked_at'])
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('type', 'startup');
});

it('returns 503 when startup checks fail', function (): void {
    $failingCheck = new class implements \Cbox\LaravelHealth\Contracts\HealthCheck
    {
        public function name(): string
        {
            return 'startup_failing';
        }

        public function run(): \Cbox\LaravelHealth\DataTransferObjects\CheckResult
        {
            return \Cbox\LaravelHealth\DataTransferObjects\CheckResult::critical('startup_failing', 'Not ready');
        }
    };

    config()->set('health.checks.startup', [$failingCheck::class]);
    app()->bind($failingCheck::class, fn () => $failingCheck);

    $response = $this->getJson('/health/startup');

    $response->assertStatus(503)
        ->assertJsonPath('status', 'critical');
});

it('requires authentication when not public', function (): void {
    LaravelHealth::$authUsing = null;
    config()->set('health.security.token', 'secret');
    config()->set('app.env', 'production');

    $response = $this->getJson('/health/startup');

    $response->assertForbidden();
});
