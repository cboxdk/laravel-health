<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Config;

use Cbox\LaravelHealth\Exceptions\InvalidConfigurationException;

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
        $prometheusNamespace = TypedConfig::string('health.metrics.prometheus.namespace', 'app');

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $prometheusNamespace) !== 1) {
            throw InvalidConfigurationException::invalidPrometheusNamespace($prometheusNamespace);
        }

        return new self(
            enabled: TypedConfig::boolean('health.enabled', true),
            prefix: TypedConfig::string('health.endpoints.prefix', 'health'),
            prometheusNamespace: $prometheusNamespace,
            security: [
                'token' => TypedConfig::nullableString('health.security.token'),
                'allowed_ips' => TypedConfig::nullableStringList('health.security.allowed_ips'),
                'public_endpoints' => TypedConfig::stringList('health.security.public_endpoints'),
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    public function publicEndpoints(): array
    {
        $endpoints = $this->security['public_endpoints'] ?? [];

        return is_array($endpoints) ? array_values(array_filter($endpoints, is_string(...))) : [];
    }

    public function token(): ?string
    {
        $token = $this->security['token'] ?? null;

        return is_string($token) ? $token : null;
    }

    /**
     * @return array<int, string>|null
     */
    public function allowedIps(): ?array
    {
        $allowedIps = $this->security['allowed_ips'] ?? null;

        // Non-string entries are dropped, never trusted: an allowlist only narrows.
        return is_array($allowedIps) ? array_values(array_filter($allowedIps, is_string(...))) : null;
    }
}
