<?php

declare(strict_types=1);

use Cbox\LaravelHealth\LaravelHealth;

beforeEach(function (): void {
    config()->set('health.security.public_endpoints', ['liveness']);
    config()->set('health.security.token', null);
    config()->set('health.cache.enabled', false);
    config()->set('health.checks.liveness', []);
    LaravelHealth::$authUsing = fn () => true;
});

afterEach(function (): void {
    LaravelHealth::$authUsing = null;
});

it('allows all IPs when no restriction set', function (): void {
    config()->set('health.security.allowed_ips', null);

    $response = $this->getJson('/health');

    $response->assertOk();
});

it('allows whitelisted IPs', function (): void {
    config()->set('health.security.allowed_ips', ['127.0.0.1']);

    $response = $this->getJson('/health');

    $response->assertOk();
});

it('blocks non-whitelisted IPs', function (): void {
    config()->set('health.security.allowed_ips', ['10.0.0.1']);

    $response = $this->getJson('/health');

    $response->assertForbidden();
});
