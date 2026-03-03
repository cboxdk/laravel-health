<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Contracts;

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;

interface HealthCheck
{
    public function name(): string;

    public function run(): CheckResult;
}
