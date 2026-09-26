<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Config\HealthConfig;
use Cbox\LaravelHealth\Exceptions\HealthException;
use Cbox\LaravelHealth\Exceptions\InvalidConfigurationException;

it('builds from config', function (): void {
    config()->set('health.endpoints.prefix', 'ops');
    config()->set('health.metrics.prometheus.namespace', 'shop_api');
    config()->set('health.security.token', 'secret');
    config()->set('health.security.allowed_ips', ['10.0.0.0/8']);
    config()->set('health.security.public_endpoints', ['liveness', 'readiness']);

    $config = HealthConfig::fromConfig();

    expect($config->prefix)->toBe('ops')
        ->and($config->prometheusNamespace)->toBe('shop_api')
        ->and($config->token())->toBe('secret')
        ->and($config->allowedIps())->toBe(['10.0.0.0/8'])
        ->and($config->publicEndpoints())->toBe(['liveness', 'readiness']);
});

it('rejects a Prometheus namespace that would produce invalid metric names', function (string $namespace): void {
    config()->set('health.metrics.prometheus.namespace', $namespace);

    expect(fn () => HealthConfig::fromConfig())
        ->toThrow(InvalidConfigurationException::class, 'health.metrics.prometheus.namespace');
})->with(['my-app', '1app', 'app name', '']);

it('drops non-string entries from security lists instead of trusting them', function (): void {
    $config = new HealthConfig(
        enabled: true,
        prefix: 'health',
        prometheusNamespace: 'app',
        security: ['allowed_ips' => ['10.0.0.1', 42, null], 'public_endpoints' => 'liveness', 'token' => 123],
    );

    expect($config->allowedIps())->toBe(['10.0.0.1'])
        ->and($config->publicEndpoints())->toBe([])
        ->and($config->token())->toBeNull();
});

it('marks every package exception with HealthException', function (): void {
    expect(InvalidConfigurationException::wrongType('health.x', 'a string', 1))->toBeInstanceOf(HealthException::class);
});
