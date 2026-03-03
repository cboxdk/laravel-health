<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Services;

use Cbox\SystemMetrics\DTO\Metrics\Container\ContainerLimits;
use Cbox\SystemMetrics\DTO\Metrics\LoadAverageSnapshot;
use Cbox\SystemMetrics\DTO\Metrics\Memory\MemorySnapshot;
use Cbox\SystemMetrics\DTO\Metrics\Network\NetworkSnapshot;
use Cbox\SystemMetrics\DTO\Metrics\Storage\StorageSnapshot;
use Cbox\SystemMetrics\DTO\Metrics\SystemLimits;
use Cbox\SystemMetrics\DTO\Metrics\UptimeSnapshot;
use Cbox\SystemMetrics\DTO\SystemOverview;
use Cbox\SystemMetrics\SystemMetrics;

class SystemMetricsService
{
    /**
     * Collect all enabled system metrics.
     *
     * @return array<string, mixed>
     */
    public function collect(): array
    {
        $overview = $this->getOverview();

        if ($overview === null) {
            return [];
        }

        $metrics = [];

        $metrics['environment'] = [
            'os' => $overview->environment->os->name,
            'os_version' => $overview->environment->os->version,
            'kernel' => $overview->environment->kernel->release,
            'architecture' => $overview->environment->architecture->kind->value,
            'containerized' => $overview->limits?->isContainerized() ?? false,
        ];

        if (config('health.metrics.system.load', true) && $overview->loadAverage !== null) {
            $metrics['load'] = [
                'load_1m' => $overview->loadAverage->oneMinute,
                'load_5m' => $overview->loadAverage->fiveMinutes,
                'load_15m' => $overview->loadAverage->fifteenMinutes,
                'core_count' => $overview->limits !== null ? $overview->limits->cpuCores : $overview->cpu->coreCount(),
            ];
        }

        if (config('health.metrics.system.memory', true)) {
            $metrics['memory'] = $this->buildMemory($overview);
        }

        if (config('health.metrics.system.storage', true) && $overview->storage !== null) {
            $metrics['storage'] = $this->buildStorage($overview->storage);
        }

        if (config('health.metrics.system.network', true) && $overview->network !== null) {
            $metrics['network'] = $this->buildNetwork($overview->network);
        }

        if ($overview->uptime !== null) {
            $metrics['uptime'] = [
                'total_seconds' => $overview->uptime->totalSeconds,
                'human_readable' => $overview->uptime->humanReadable(),
            ];
        }

        if ($overview->container !== null) {
            $metrics['container'] = $this->buildContainer($overview->container, $overview);
        }

        return $metrics;
    }

    public function getOverview(): ?SystemOverview
    {
        $result = SystemMetrics::overview();

        return $result->isSuccess() ? $result->getValue() : null;
    }

    public function getLoadAverage(): ?LoadAverageSnapshot
    {
        return $this->getOverview()?->loadAverage;
    }

    public function getMemory(): ?MemorySnapshot
    {
        return $this->getOverview()?->memory;
    }

    public function getLimits(): ?SystemLimits
    {
        return $this->getOverview()?->limits;
    }

    public function getStorage(): ?StorageSnapshot
    {
        return $this->getOverview()?->storage;
    }

    public function getNetwork(): ?NetworkSnapshot
    {
        return $this->getOverview()?->network;
    }

    public function getUptime(): ?UptimeSnapshot
    {
        return $this->getOverview()?->uptime;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMemory(SystemOverview $overview): array
    {
        $limits = $overview->limits;

        // Use limits API when available — respects cgroup limits in containers
        if ($limits !== null) {
            return [
                'total_bytes' => $limits->memoryBytes,
                'used_bytes' => (int) $limits->currentMemoryBytes,
                'available_bytes' => $limits->availableMemoryBytes(),
                'used_percent' => round($limits->memoryUtilization(), 1),
                'swap_total_bytes' => $limits->swapBytes,
                'swap_used_bytes' => $limits->currentSwapBytes !== null ? (int) $limits->currentSwapBytes : null,
                'source' => $limits->source->value,
            ];
        }

        $memory = $overview->memory;

        return [
            'total_bytes' => $memory->totalBytes,
            'used_bytes' => $memory->usedBytes,
            'available_bytes' => $memory->availableBytes,
            'used_percent' => round($memory->usedPercentage(), 1),
            'swap_total_bytes' => $memory->swapTotalBytes,
            'swap_used_bytes' => $memory->swapUsedBytes,
            'source' => 'host',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildStorage(StorageSnapshot $storage): array
    {
        $mounts = [];

        foreach ($storage->mountPoints as $mount) {
            $mounts[] = [
                'mountpoint' => $mount->mountPoint,
                'device' => $mount->device,
                'total_bytes' => $mount->totalBytes,
                'used_bytes' => $mount->usedBytes,
                'available_bytes' => $mount->availableBytes,
                'used_percent' => round($mount->usedPercentage(), 1),
            ];
        }

        return $mounts;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildNetwork(NetworkSnapshot $network): array
    {
        $interfaces = [];

        foreach ($network->interfaces as $iface) {
            $interfaces[] = [
                'name' => $iface->name,
                'is_up' => $iface->isUp,
                'rx_bytes' => $iface->stats->bytesReceived,
                'tx_bytes' => $iface->stats->bytesSent,
                'rx_errors' => $iface->stats->receiveErrors,
                'tx_errors' => $iface->stats->transmitErrors,
            ];
        }

        return $interfaces;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContainer(ContainerLimits $container, SystemOverview $overview): array
    {
        return [
            'cgroup_version' => $container->cgroupVersion->value,
            'cpu_quota' => $container->cpuQuota,
            'memory_limit_bytes' => $container->memoryLimitBytes,
            'cpu_usage_cores' => $container->cpuUsageCores,
            'memory_usage_bytes' => $container->memoryUsageBytes,
            'cpu_throttled_count' => $container->cpuThrottledCount,
            'oom_kill_count' => $container->oomKillCount,
            'host_cpu_cores' => $overview->cpu->coreCount(),
            'host_memory_bytes' => $overview->memory->totalBytes,
        ];
    }
}
