<?php

declare(strict_types=1);

use Cbox\LaravelHealth\LaravelHealth;

beforeEach(function (): void {
    config()->set('health.security.public_endpoints', ['liveness']);
    config()->set('health.security.token', null);
    config()->set('health.security.allowed_ips', null);
    config()->set('health.cache.enabled', false);
    config()->set('health.checks.liveness', []);
    config()->set('health.checks.readiness', []);
    LaravelHealth::$authUsing = fn () => true;
});

afterEach(function (): void {
    LaravelHealth::$authUsing = null;
});

it('returns prometheus format', function (): void {
    $response = $this->get('/health/metrics');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
});
