<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\SystemMetrics\SystemMetrics;

final class MemoryCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        $result = SystemMetrics::memory();

        if ($result->isFailure()) {
            return CheckResult::unknown($this->name(), 'Unable to read memory metrics');
        }

        $memory = $result->getValue();
        $usedPercent = $memory->usedPercentage();

        /** @var int|float $threshold */
        $threshold = config('health.thresholds.memory_percent', 90);

        $metadata = [
            'used_percent' => round($usedPercent, 1),
            'used_bytes' => $memory->usedBytes,
            'total_bytes' => $memory->totalBytes,
            'threshold' => $threshold,
        ];

        if ($usedPercent >= (float) $threshold) {
            return CheckResult::critical(
                $this->name(),
                sprintf('Memory usage %.1f%% exceeds threshold %s%%', $usedPercent, $threshold),
                $metadata,
            );
        }

        return CheckResult::ok($this->name(), 'OK', $metadata);
    }
}
