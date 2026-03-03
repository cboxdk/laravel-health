<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class RedisCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        try {
            /** @var string $connection */
            $connection = config('health.checks_config.redis.connection', 'default');

            /** @var mixed $response */
            $response = Redis::connection($connection)->command('ping');

            if ($response === true || $response === 'PONG' || $response === '+PONG') {
                return CheckResult::ok($this->name());
            }

            return CheckResult::critical($this->name(), 'Unexpected ping response');
        } catch (Throwable $e) {
            return CheckResult::critical($this->name(), $e->getMessage());
        }
    }
}
