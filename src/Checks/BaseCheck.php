<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\Contracts\HealthCheck;
use Illuminate\Support\Str;

abstract class BaseCheck implements HealthCheck
{
    public function name(): string
    {
        // Remove 'Check' suffix and convert to snake_case
        $name = Str::replaceEnd('Check', '', class_basename(static::class));

        return $name === '' ? 'unknown' : Str::snake($name);
    }
}
