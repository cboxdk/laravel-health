<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Illuminate\Support\Facades\Queue;
use Throwable;

final class QueueCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        try {
            /** @var string|null $connection */
            $connection = config('health.checks_config.queue.connection');

            $size = Queue::connection($connection)->size();

            return CheckResult::ok($this->name(), 'OK', ['queue_size' => $size]);
        } catch (Throwable $e) {
            return CheckResult::critical($this->name(), $e->getMessage());
        }
    }
}
