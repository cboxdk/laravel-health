<?php

declare(strict_types=1);

use Cbox\LaravelHealth\LaravelHealth;

beforeEach(function (): void {
    config()->set('health.security.public_endpoints', ['liveness']);
    config()->set('health.security.allowed_ips', null);
    config()->set('health.cache.enabled', false);
    config()->set('health.checks.liveness', []);
    config()->set('health.checks.readiness', []);
    LaravelHealth::$authUsing = null;
});

afterEach(function (): void {
    LaravelHealth::$authUsing = null;
});

it('allows public endpoints without auth', function (): void {
    config()->set('health.security.token', 'secret');

    $response = $this->getJson('/health');

    $response->assertOk();
});

it('blocks non-public endpoints without token in non-local', function (): void {
    config()->set('health.security.token', 'secret');
    app()->detectEnvironment(fn () => 'production');

    $response = $this->getJson('/health/ready');

    $response->assertForbidden();
});

it('allows access with valid token in query param', function (): void {
    config()->set('health.security.token', 'secret');
    app()->detectEnvironment(fn () => 'production');

    $response = $this->getJson('/health/ready?token=secret');

    $response->assertOk();
});

it('allows access with valid bearer token', function (): void {
    config()->set('health.security.token', 'secret');
    app()->detectEnvironment(fn () => 'production');

    $response = $this->getJson('/health/ready', [
        'Authorization' => 'Bearer secret',
    ]);

    $response->assertOk();
});

it('allows access with auth callback', function (): void {
    config()->set('health.security.token', null);
    LaravelHealth::auth(fn () => true);

    $response = $this->getJson('/health/ready');

    $response->assertOk();
});

it('blocks with invalid token', function (): void {
    config()->set('health.security.token', 'secret');
    app()->detectEnvironment(fn () => 'production');

    $response = $this->getJson('/health/ready?token=wrong');

    $response->assertForbidden();
});
