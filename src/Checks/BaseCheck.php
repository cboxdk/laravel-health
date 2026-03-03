<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\Contracts\HealthCheck;

abstract class BaseCheck implements HealthCheck
{
    public function name(): string
    {
        $className = class_basename(static::class);

        // Remove 'Check' suffix and convert to snake_case
        $name = preg_replace('/Check$/', '', $className);

        if ($name === null || $name === '') {
            return 'unknown';
        }

        $snakeCase = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $name));

        return $snakeCase;
    }
}
