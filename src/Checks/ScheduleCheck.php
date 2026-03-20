<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class ScheduleCheck extends BaseCheck
{
    /**
     * Cache key used for the scheduler heartbeat.
     * Should be written by a scheduled task: Cache::put('health:schedule:heartbeat', now())
     */
    private const string HEARTBEAT_KEY = 'health:schedule:heartbeat';

    public function run(): CheckResult
    {
        try {
            /** @var int $maxAgeMinutes */
            $maxAgeMinutes = config('health.checks_config.schedule.max_age_minutes', 5);

            /** @var \Illuminate\Support\Carbon|null $lastHeartbeat */
            $lastHeartbeat = Cache::get(self::HEARTBEAT_KEY);

            if ($lastHeartbeat === null) {
                return CheckResult::warning(
                    $this->name(),
                    'No scheduler heartbeat found. Ensure a scheduled task writes to the heartbeat cache key.',
                );
            }

            $ageMinutes = (int) $lastHeartbeat->diffInMinutes(now());

            if ($ageMinutes > $maxAgeMinutes) {
                return CheckResult::critical(
                    $this->name(),
                    "Scheduler heartbeat is {$ageMinutes} minutes old (max: {$maxAgeMinutes})",
                    ['age_minutes' => $ageMinutes, 'max_age_minutes' => $maxAgeMinutes],
                );
            }

            return CheckResult::ok($this->name(), 'OK', ['age_minutes' => $ageMinutes]);
        } catch (Throwable $e) {
            return CheckResult::critical($this->name(), $e->getMessage());
        }
    }
}
