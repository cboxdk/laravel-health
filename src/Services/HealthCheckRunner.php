<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Services;

use Cbox\LaravelHealth\Config\TypedConfig;
use Cbox\LaravelHealth\Contracts\HealthCheck;
use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Exceptions\InvalidCheckException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class HealthCheckRunner implements RunsHealthChecks
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function run(EndpointType $type): HealthReport
    {
        $cacheKey = "health:report:{$type->value}";

        $cacheEnabled = TypedConfig::boolean('health.cache.enabled', true);
        $cacheTtl = TypedConfig::integer('health.cache.ttl', 10);
        $cacheStore = TypedConfig::nullableString('health.cache.store');

        if ($cacheEnabled) {
            try {
                $cached = Cache::store($cacheStore)->get($cacheKey);

                if ($cached instanceof HealthReport) {
                    return $cached;
                }
            } catch (Throwable) {
                // Cache unavailable, fall through to execute checks
            }
        }

        $report = $this->execute($type);

        if ($cacheEnabled) {
            try {
                Cache::store($cacheStore)->put($cacheKey, $report, $cacheTtl);
            } catch (Throwable) {
                // Cache unavailable, skip caching
            }
        }

        return $report;
    }

    private function execute(EndpointType $type): HealthReport
    {
        $checkClasses = TypedConfig::stringList("health.checks.{$type->value}");

        $results = [];
        $totalStart = hrtime(true);

        foreach ($checkClasses as $checkClass) {
            $results[] = $this->runCheck($checkClass);
        }

        $totalDurationMs = (hrtime(true) - $totalStart) / 1e6;

        return HealthReport::fromResults($type, $results, $totalDurationMs);
    }

    private function runCheck(string $checkClass): CheckResult
    {
        $start = hrtime(true);

        try {
            if (! class_exists($checkClass)) {
                return CheckResult::critical($checkClass, InvalidCheckException::classNotFound($checkClass)->getMessage());
            }

            $check = $this->container->make($checkClass);

            if (! $check instanceof HealthCheck) {
                return CheckResult::critical($checkClass, InvalidCheckException::notImplementingContract($checkClass)->getMessage());
            }

            $result = $check->run();
        } catch (Throwable $e) {
            $durationMs = (hrtime(true) - $start) / 1e6;

            return CheckResult::critical($checkClass, $e->getMessage())
                ->withDuration($durationMs);
        }

        $durationMs = (hrtime(true) - $start) / 1e6;

        return $result->withDuration($durationMs);
    }
}
