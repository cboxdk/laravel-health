<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Exceptions;

use InvalidArgumentException;

final class InvalidConfigurationException extends InvalidArgumentException implements HealthException
{
    public static function wrongType(string $key, string $expected, mixed $value): self
    {
        return new self(sprintf(
            'Configuration value [%s] must be %s, %s given.',
            $key,
            $expected,
            get_debug_type($value),
        ));
    }

    public static function invalidPrometheusNamespace(string $namespace): self
    {
        return new self(sprintf(
            'Configuration value [health.metrics.prometheus.namespace] must be a valid Prometheus metric name prefix (letters, digits and underscores, not starting with a digit), "%s" given.',
            $namespace,
        ));
    }
}
