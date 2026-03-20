<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Services;

use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\SystemMetrics\DTO\SystemOverview;

final class PrometheusRenderer
{
    public function __construct(
        private readonly string $namespace = 'app',
    ) {}

    public function render(
        HealthReport $livenessReport,
        HealthReport $readinessReport,
        SystemMetricsService $metricsService,
    ): string {
        $lines = [];

        $this->renderHealthChecks($lines, $livenessReport, $readinessReport);
        $this->renderSystemMetrics($lines, $metricsService);

        return implode("\n", $lines)."\n";
    }

    /** @param list<string> $lines */
    private function renderHealthChecks(array &$lines, HealthReport $livenessReport, HealthReport $readinessReport): void
    {
        $this->comment($lines, "{$this->namespace}_health_check_status", 'gauge', 'Health check status (1=ok, 0.5=warning, 0=critical)');

        $allResults = array_merge($livenessReport->results, $readinessReport->results);
        $seen = [];

        foreach ($allResults as $result) {
            if (isset($seen[$result->name])) {
                continue;
            }
            $seen[$result->name] = true;

            $this->gauge(
                $lines,
                "{$this->namespace}_health_check_status",
                $result->status->numericValue(),
                ['check' => $result->name],
            );
        }

        $lines[] = '';
        $this->comment($lines, "{$this->namespace}_health_check_duration_seconds", 'gauge', 'Health check duration in seconds');

        $seen = [];
        foreach ($allResults as $result) {
            if (isset($seen[$result->name])) {
                continue;
            }
            $seen[$result->name] = true;

            $this->gauge(
                $lines,
                "{$this->namespace}_health_check_duration_seconds",
                $result->durationMs / 1000,
                ['check' => $result->name],
            );
        }
    }

    /** @param list<string> $lines */
    private function renderSystemMetrics(array &$lines, SystemMetricsService $metricsService): void
    {
        $overview = $metricsService->getOverview();

        if ($overview === null) {
            return;
        }

        $this->renderLoadMetrics($lines, $overview);
        $this->renderMemoryMetrics($lines, $overview);
        $this->renderStorageMetrics($lines, $overview);
        $this->renderNetworkMetrics($lines, $overview);
        $this->renderUptimeMetrics($lines, $overview);
        $this->renderContainerMetrics($lines, $overview);
    }

    /** @param list<string> $lines */
    private function renderLoadMetrics(array &$lines, SystemOverview $overview): void
    {
        if (! config('health.metrics.system.load', true)) {
            return;
        }

        $load = $overview->loadAverage;

        if ($load === null) {
            return;
        }

        $lines[] = '';
        $this->comment($lines, "{$this->namespace}_system_cpu_load_1m", 'gauge', 'System CPU load average 1 minute');
        $this->gauge($lines, "{$this->namespace}_system_cpu_load_1m", $load->oneMinute);

        $this->comment($lines, "{$this->namespace}_system_cpu_load_5m", 'gauge', 'System CPU load average 5 minutes');
        $this->gauge($lines, "{$this->namespace}_system_cpu_load_5m", $load->fiveMinutes);

        $this->comment($lines, "{$this->namespace}_system_cpu_load_15m", 'gauge', 'System CPU load average 15 minutes');
        $this->gauge($lines, "{$this->namespace}_system_cpu_load_15m", $load->fifteenMinutes);
    }

    /** @param list<string> $lines */
    private function renderMemoryMetrics(array &$lines, SystemOverview $overview): void
    {
        if (! config('health.metrics.system.memory', true)) {
            return;
        }

        $limits = $overview->limits;

        if ($limits !== null) {
            $lines[] = '';
            $this->comment($lines, "{$this->namespace}_system_memory_used_bytes", 'gauge', 'System memory used in bytes');
            $this->gauge($lines, "{$this->namespace}_system_memory_used_bytes", $limits->currentMemoryBytes);

            $this->comment($lines, "{$this->namespace}_system_memory_total_bytes", 'gauge', 'System memory total in bytes');
            $this->gauge($lines, "{$this->namespace}_system_memory_total_bytes", (float) $limits->memoryBytes);

            $this->comment($lines, "{$this->namespace}_system_memory_usage_ratio", 'gauge', 'System memory usage ratio (0-1)');
            $this->gauge($lines, "{$this->namespace}_system_memory_usage_ratio", $limits->memoryBytes > 0 ? $limits->currentMemoryBytes / $limits->memoryBytes : 0.0);

            return;
        }

        $memory = $overview->memory;

        $lines[] = '';
        $this->comment($lines, "{$this->namespace}_system_memory_used_bytes", 'gauge', 'System memory used in bytes');
        $this->gauge($lines, "{$this->namespace}_system_memory_used_bytes", (float) $memory->usedBytes);

        $this->comment($lines, "{$this->namespace}_system_memory_total_bytes", 'gauge', 'System memory total in bytes');
        $this->gauge($lines, "{$this->namespace}_system_memory_total_bytes", (float) $memory->totalBytes);

        $this->comment($lines, "{$this->namespace}_system_memory_usage_ratio", 'gauge', 'System memory usage ratio (0-1)');
        $this->gauge($lines, "{$this->namespace}_system_memory_usage_ratio", $memory->totalBytes > 0 ? $memory->usedBytes / $memory->totalBytes : 0.0);
    }

    /** @param list<string> $lines */
    private function renderStorageMetrics(array &$lines, SystemOverview $overview): void
    {
        if (! config('health.metrics.system.storage', true)) {
            return;
        }

        $storage = $overview->storage;

        if ($storage === null) {
            return;
        }

        $lines[] = '';
        $this->comment($lines, "{$this->namespace}_system_disk_used_bytes", 'gauge', 'Disk used bytes per mount point');

        foreach ($storage->mountPoints as $mount) {
            $labels = ['mountpoint' => $mount->mountPoint];
            $this->gauge($lines, "{$this->namespace}_system_disk_used_bytes", (float) $mount->usedBytes, $labels);
        }

        $this->comment($lines, "{$this->namespace}_system_disk_total_bytes", 'gauge', 'Disk total bytes per mount point');

        foreach ($storage->mountPoints as $mount) {
            $labels = ['mountpoint' => $mount->mountPoint];
            $this->gauge($lines, "{$this->namespace}_system_disk_total_bytes", (float) $mount->totalBytes, $labels);
        }

        $this->comment($lines, "{$this->namespace}_system_disk_usage_ratio", 'gauge', 'Disk usage ratio per mount point (0-1)');

        foreach ($storage->mountPoints as $mount) {
            $labels = ['mountpoint' => $mount->mountPoint];
            $ratio = $mount->totalBytes > 0 ? $mount->usedBytes / $mount->totalBytes : 0.0;
            $this->gauge($lines, "{$this->namespace}_system_disk_usage_ratio", $ratio, $labels);
        }
    }

    /** @param list<string> $lines */
    private function renderNetworkMetrics(array &$lines, SystemOverview $overview): void
    {
        if (! config('health.metrics.system.network', true)) {
            return;
        }

        $network = $overview->network;

        if ($network === null) {
            return;
        }

        $lines[] = '';
        $this->comment($lines, "{$this->namespace}_system_network_rx_bytes_total", 'counter', 'Network bytes received per interface');

        foreach ($network->interfaces as $iface) {
            $labels = ['interface' => $iface->name];
            $this->gauge($lines, "{$this->namespace}_system_network_rx_bytes_total", (float) $iface->stats->bytesReceived, $labels);
        }

        $this->comment($lines, "{$this->namespace}_system_network_tx_bytes_total", 'counter', 'Network bytes transmitted per interface');

        foreach ($network->interfaces as $iface) {
            $labels = ['interface' => $iface->name];
            $this->gauge($lines, "{$this->namespace}_system_network_tx_bytes_total", (float) $iface->stats->bytesSent, $labels);
        }
    }

    /** @param list<string> $lines */
    private function renderUptimeMetrics(array &$lines, SystemOverview $overview): void
    {
        $uptime = $overview->uptime;

        if ($uptime === null) {
            return;
        }

        $lines[] = '';
        $this->comment($lines, "{$this->namespace}_system_uptime_seconds", 'gauge', 'System uptime in seconds');
        $this->gauge($lines, "{$this->namespace}_system_uptime_seconds", (float) $uptime->totalSeconds);
    }

    /** @param list<string> $lines */
    private function renderContainerMetrics(array &$lines, SystemOverview $overview): void
    {
        $container = $overview->container;

        if ($container === null) {
            return;
        }

        $lines[] = '';

        if ($container->hasMemoryLimit()) {
            $this->comment($lines, "{$this->namespace}_container_memory_limit_bytes", 'gauge', 'Container memory limit in bytes');
            $this->gauge($lines, "{$this->namespace}_container_memory_limit_bytes", (float) $container->memoryLimitBytes);

            if ($container->memoryUsageBytes !== null) {
                $this->comment($lines, "{$this->namespace}_container_memory_usage_bytes", 'gauge', 'Container memory usage in bytes');
                $this->gauge($lines, "{$this->namespace}_container_memory_usage_bytes", (float) $container->memoryUsageBytes);
            }
        }

        if ($container->hasCpuLimit()) {
            $this->comment($lines, "{$this->namespace}_container_cpu_quota", 'gauge', 'Container CPU quota (cores)');
            $this->gauge($lines, "{$this->namespace}_container_cpu_quota", (float) $container->cpuQuota);
        }

        if ($container->cpuThrottledCount !== null) {
            $this->comment($lines, "{$this->namespace}_container_cpu_throttled_total", 'counter', 'Container CPU throttled count');
            $this->gauge($lines, "{$this->namespace}_container_cpu_throttled_total", (float) $container->cpuThrottledCount);
        }

        if ($container->oomKillCount !== null) {
            $this->comment($lines, "{$this->namespace}_container_oom_kills_total", 'counter', 'Container OOM kill count');
            $this->gauge($lines, "{$this->namespace}_container_oom_kills_total", (float) $container->oomKillCount);
        }
    }

    /** @param list<string> $lines */
    private function comment(array &$lines, string $name, string $type, string $help): void
    {
        $lines[] = "# HELP {$name} {$help}";
        $lines[] = "# TYPE {$name} {$type}";
    }

    /**
     * @param  list<string>  $lines
     * @param  array<string, string>  $labels
     */
    private function gauge(array &$lines, string $name, float $value, array $labels = []): void
    {
        $labelStr = '';

        if ($labels !== []) {
            $parts = [];
            foreach ($labels as $key => $val) {
                $escaped = str_replace(['\\', '"', "\n"], ['\\\\', '\\"', '\\n'], $val);
                $parts[] = "{$key}=\"{$escaped}\"";
            }
            $labelStr = '{'.implode(',', $parts).'}';
        }

        // Format value: use integer representation for whole numbers
        if ($value == floor($value) && abs($value) < 1e15) {
            $formatted = number_format($value, 1, '.', '');
        } else {
            $formatted = rtrim(rtrim(sprintf('%.6f', $value), '0'), '.');
        }

        $lines[] = "{$name}{$labelStr} {$formatted}";
    }
}
