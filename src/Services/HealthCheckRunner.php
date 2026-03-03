<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Services;

use Cbox\LaravelHealth\Contracts\HealthCheck;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Enums\Status;
use Cbox\LaravelHealth\Exceptions\InvalidCheckException;
use DateTimeImmutable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class HealthCheckRunner
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function run(EndpointType $type): HealthReport
    {
        $cacheKey = "health:report:{$type->value}";

        /** @var bool $cacheEnabled */
        $cacheEnabled = config('health.cache.enabled', true);

        /** @var int|string $cacheTtlRaw */
        $cacheTtlRaw = config('health.cache.ttl', 10);
        $cacheTtl = (int) $cacheTtlRaw;

        /** @var string|null $cacheStore */
        $cacheStore = config('health.cache.store');

        if ($cacheEnabled) {
            /** @var HealthReport|null $cached */
            $cached = Cache::store($cacheStore)->get($cacheKey);

            if ($cached instanceof HealthReport) {
                return $cached;
            }
        }

        $report = $this->execute($type);

        if ($cacheEnabled) {
            Cache::store($cacheStore)->put($cacheKey, $report, $cacheTtl);
        }

        return $report;
    }

    private function execute(EndpointType $type): HealthReport
    {
        /** @var array<int, class-string> $checkClasses */
        $checkClasses = config("health.checks.{$type->value}", []);

        $results = [];
        $totalStart = hrtime(true);

        foreach ($checkClasses as $checkClass) {
            $results[] = $this->runCheck($checkClass);
        }

        $totalDurationMs = (hrtime(true) - $totalStart) / 1e6;

        $statuses = array_map(fn (CheckResult $r): Status => $r->status, $results);
        $worstStatus = $statuses !== [] ? Status::worst($statuses) : Status::Ok;

        return new HealthReport(
            type: $type,
            status: $worstStatus,
            results: $results,
            totalDurationMs: $totalDurationMs,
            checkedAt: new DateTimeImmutable,
        );
    }

    /**
     * @param  class-string  $checkClass
     */
    private function runCheck(string $checkClass): CheckResult
    {
        if (! class_exists($checkClass)) {
            throw InvalidCheckException::classNotFound($checkClass);
        }

        $check = $this->container->make($checkClass);

        if (! $check instanceof HealthCheck) {
            throw InvalidCheckException::notImplementingContract($checkClass);
        }

        $start = hrtime(true);

        try {
            $result = $check->run();
        } catch (Throwable $e) {
            $durationMs = (hrtime(true) - $start) / 1e6;

            return CheckResult::critical($check->name(), $e->getMessage())
                ->withDuration($durationMs);
        }

        $durationMs = (hrtime(true) - $start) / 1e6;

        return $result->withDuration($durationMs);
    }
}
