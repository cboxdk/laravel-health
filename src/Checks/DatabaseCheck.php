<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\Config\TypedConfig;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Illuminate\Support\Facades\DB;
use Throwable;

final class DatabaseCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        try {
            $connection = TypedConfig::nullableString('health.checks_config.database.connection');

            DB::connection($connection)->getPdo();

            return CheckResult::ok($this->name());
        } catch (Throwable $e) {
            return CheckResult::critical($this->name(), $e->getMessage());
        }
    }
}
