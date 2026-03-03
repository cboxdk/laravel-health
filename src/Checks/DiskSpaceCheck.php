<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\SystemMetrics\SystemMetrics;

final class DiskSpaceCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        $result = SystemMetrics::storage();

        if ($result->isFailure()) {
            return CheckResult::unknown($this->name(), 'Unable to read storage metrics');
        }

        $storage = $result->getValue();

        /** @var int|float $threshold */
        $threshold = config('health.thresholds.disk_space_percent', 90);

        $criticalMounts = [];

        foreach ($storage->mountPoints as $mount) {
            $usedPercent = $mount->usedPercentage();

            if ($usedPercent >= (float) $threshold) {
                $criticalMounts[] = sprintf(
                    '%s: %.1f%%',
                    $mount->mountPoint,
                    $usedPercent,
                );
            }
        }

        if ($criticalMounts !== []) {
            return CheckResult::critical(
                $this->name(),
                'Disk usage exceeds threshold: '.implode(', ', $criticalMounts),
                ['threshold' => $threshold, 'critical_mounts' => $criticalMounts],
            );
        }

        return CheckResult::ok($this->name(), 'OK', ['threshold' => $threshold]);
    }
}
