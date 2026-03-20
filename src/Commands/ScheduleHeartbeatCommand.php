<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class ScheduleHeartbeatCommand extends Command
{
    protected $signature = 'health:heartbeat';

    protected $description = 'Write a scheduler heartbeat timestamp to cache';

    public function handle(): int
    {
        Cache::put('health:schedule:heartbeat', now(), 600);

        return self::SUCCESS;
    }
}
