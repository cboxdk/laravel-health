<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\Config\TypedConfig;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;

final class EnvironmentCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        $required = TypedConfig::stringList('health.checks_config.environment.required');

        $missing = [];

        foreach ($required as $var) {
            $value = getenv($var);
            if ($value === false) {
                $missing[] = $var;
            }
        }

        if ($missing !== []) {
            return CheckResult::critical(
                $this->name(),
                'Missing required environment variables: '.implode(', ', $missing),
                ['missing' => $missing],
            );
        }

        return CheckResult::ok($this->name());
    }
}
