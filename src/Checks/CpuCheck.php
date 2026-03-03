<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\SystemMetrics\SystemMetrics;

/**
 * Checks CPU load average normalized by core count.
 *
 * Uses load average (instantaneous) rather than cpuUsage() which blocks for 1+ second.
 */
final class CpuCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        $loadResult = SystemMetrics::loadAverage();

        if ($loadResult->isFailure()) {
            return CheckResult::unknown($this->name(), 'Unable to read load average');
        }

        $cpuResult = SystemMetrics::cpu();

        if ($cpuResult->isFailure()) {
            return CheckResult::unknown($this->name(), 'Unable to read CPU metrics');
        }

        $load = $loadResult->getValue();
        $cpu = $cpuResult->getValue();
        $normalized = $load->normalized($cpu);

        /** @var float|int $thresholdRaw */
        $thresholdRaw = config('health.thresholds.cpu_load_per_core', 2.0);
        $threshold = (float) $thresholdRaw;

        $metadata = [
            'load_1m' => $load->oneMinute,
            'load_5m' => $load->fiveMinutes,
            'load_15m' => $load->fifteenMinutes,
            'cores' => $cpu->coreCount(),
            'normalized_1m' => round($normalized->oneMinute, 2),
            'threshold_per_core' => $threshold,
        ];

        if ($normalized->oneMinute >= $threshold) {
            return CheckResult::critical(
                $this->name(),
                sprintf(
                    'Load per core %.2f exceeds threshold %.1f (load: %.2f, cores: %d)',
                    $normalized->oneMinute,
                    $threshold,
                    $load->oneMinute,
                    $cpu->coreCount(),
                ),
                $metadata,
            );
        }

        return CheckResult::ok($this->name(), 'OK', $metadata);
    }
}
