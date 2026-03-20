<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Config;

final readonly class HealthConfig
{
    /**
     * @param  array<string, mixed>  $security
     */
    public function __construct(
        public bool $enabled,
        public string $prefix,
        public string $prometheusNamespace,
        public array $security,
    ) {}

    public static function fromConfig(): self
    {
        /** @var string $prefix */
        $prefix = config('health.endpoints.prefix', 'health');

        /** @var string $prometheusNamespace */
        $prometheusNamespace = config('health.metrics.prometheus.namespace', 'app');

        /** @var array<string, mixed> $security */
        $security = config('health.security', []);

        return new self(
            enabled: (bool) config('health.enabled', true),
            prefix: $prefix,
            prometheusNamespace: $prometheusNamespace,
            security: $security,
        );
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
