<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\Config\TypedConfig;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\SystemMetrics\SystemMetrics;

/**
 * Checks memory usage against a percentage threshold.
 *
 * Container-aware: inside a cgroup-limited container the usage is measured
 * against the container's memory limit, not the host's RAM. Falls back to
 * host memory when limits cannot be read.
 */
final class MemoryCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        $limitsResult = SystemMetrics::limits();

        if ($limitsResult->isSuccess()) {
            $limits = $limitsResult->getValue();

            return $this->evaluate(
                usedPercent: $limits->memoryUtilization(),
                usedBytes: (int) round($limits->currentMemoryBytes),
                totalBytes: $limits->memoryBytes,
                source: $limits->source->value,
            );
        }

        $memoryResult = SystemMetrics::memory();

        if ($memoryResult->isFailure()) {
            return CheckResult::unknown($this->name(), 'Unable to read memory metrics');
        }

        $memory = $memoryResult->getValue();

        return $this->evaluate(
            usedPercent: $memory->usedPercentage(),
            usedBytes: $memory->usedBytes,
            totalBytes: $memory->totalBytes,
            source: 'host',
        );
    }

    private function evaluate(float $usedPercent, int $usedBytes, int $totalBytes, string $source): CheckResult
    {
        $threshold = TypedConfig::number('health.thresholds.memory_percent', 90);

        $metadata = [
            'used_percent' => round($usedPercent, 1),
            'used_bytes' => $usedBytes,
            'total_bytes' => $totalBytes,
            'threshold' => $threshold,
            'source' => $source,
        ];

        if ($usedPercent >= $threshold) {
            return CheckResult::critical(
                $this->name(),
                sprintf('Memory usage %.1f%% exceeds threshold %s%%', $usedPercent, $threshold),
                $metadata,
            );
        }

        return CheckResult::ok($this->name(), 'OK', $metadata);
    }
}
