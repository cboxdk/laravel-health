<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Exceptions;

use InvalidArgumentException;

final class InvalidCheckException extends InvalidArgumentException
{
    public static function notImplementingContract(string $class): self
    {
        return new self("Class [{$class}] must implement the HealthCheck contract.");
    }

    public static function classNotFound(string $class): self
    {
        return new self("Health check class [{$class}] not found.");
    }
}
