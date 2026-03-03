<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Config;

final readonly class HealthConfig
{
    /**
     * @param  array<string, array{path: string, enabled: bool}>  $endpoints
     * @param  array<string, array<int, class-string>>  $checks
     * @param  array<string, mixed>  $checksConfig
     * @param  array<string, mixed>  $security
     * @param  array<string, bool>  $systemMetrics
     * @param  array<string, mixed>  $thresholds
     * @param  array<string, mixed>  $cacheConfig
     * @param  array<int, string>  $middleware
     */
    public function __construct(
        public bool $enabled,
        public string $prefix,
        public array $endpoints,
        public array $checks,
        public array $checksConfig,
        public array $security,
        public array $middleware,
        public bool $prometheusEnabled,
        public string $prometheusNamespace,
        public array $systemMetrics,
        public array $thresholds,
        public array $cacheConfig,
    ) {}

    public static function fromConfig(): self
    {
        /** @var string $prefix */
        $prefix = config('health.endpoints.prefix', 'health');

        /** @var string $prometheusNamespace */
        $prometheusNamespace = config('health.metrics.prometheus.namespace', 'app');

        /** @var array<string, array{path: string, enabled: bool}> $endpoints */
        $endpoints = config('health.endpoints', []);

        /** @var array<string, array<int, class-string>> $checks */
        $checks = config('health.checks', []);

        /** @var array<string, mixed> $checksConfig */
        $checksConfig = config('health.checks_config', []);

        /** @var array<string, mixed> $security */
        $security = config('health.security', []);

        /** @var array<int, string> $middleware */
        $middleware = config('health.middleware', ['api']);

        /** @var array<string, bool> $systemMetrics */
        $systemMetrics = config('health.metrics.system', []);

        /** @var array<string, mixed> $thresholds */
        $thresholds = config('health.thresholds', []);

        /** @var array<string, mixed> $cacheConfig */
        $cacheConfig = config('health.cache', []);

        return new self(
            enabled: (bool) config('health.enabled', true),
            prefix: $prefix,
            endpoints: $endpoints,
            checks: $checks,
            checksConfig: $checksConfig,
            security: $security,
            middleware: $middleware,
            prometheusEnabled: (bool) config('health.metrics.prometheus.enabled', true),
            prometheusNamespace: $prometheusNamespace,
            systemMetrics: $systemMetrics,
            thresholds: $thresholds,
            cacheConfig: $cacheConfig,
        );
    }

    public function isEndpointEnabled(string $endpoint): bool
    {
        return (bool) ($this->endpoints[$endpoint]['enabled'] ?? false);
    }

    public function endpointPath(string $endpoint): string
    {
        return (string) ($this->endpoints[$endpoint]['path'] ?? '');
    }

    /**
     * @return array<int, string>
     */
    public function publicEndpoints(): array
    {
        /** @var array<int, string> */
        return $this->security['public_endpoints'] ?? [];
    }

    public function token(): ?string
    {
        /** @var string|null */
        return $this->security['token'] ?? null;
    }

    /**
     * @return array<int, string>|null
     */
    public function allowedIps(): ?array
    {
        /** @var array<int, string>|null */
        return $this->security['allowed_ips'] ?? null;
    }
}
