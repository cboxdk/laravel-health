<?php

declare(strict_types=1);

use Cbox\LaravelHealth\LaravelHealth;

beforeEach(function (): void {
    // Make liveness public (default config)
    config()->set('health.security.public_endpoints', ['liveness']);
    config()->set('health.security.token', null);
    config()->set('health.security.allowed_ips', null);
    config()->set('health.cache.enabled', false);
    // Use empty checks so we don't need real DB
    config()->set('health.checks.liveness', []);
    LaravelHealth::$authUsing = null;
});

it('returns 200 for healthy liveness', function (): void {
    $response = $this->getJson('/health');

    $response->assertOk()
        ->assertJsonStructure(['status', 'type', 'checks', 'total_duration_ms', 'checked_at'])
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('type', 'liveness');
});

it('is publicly accessible as default', function (): void {
    config()->set('health.security.token', 'secret');

    $response = $this->getJson('/health');

    $response->assertOk();
});
