<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Testing;

use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\LaravelHealth\Enums\EndpointType;
use PHPUnit\Framework\Assert;

/**
 * In-memory stand-in for the health check runner.
 *
 * Returns the reports you give it instead of running real checks, and records which
 * endpoints were run. An endpoint without a prepared report returns a healthy, empty one.
 * Install it with {@see InteractsWithHealth::fakeHealth()}.
 */
final class FakeHealthCheckRunner implements RunsHealthChecks
{
    /** @var array<string, HealthReport> */
    private array $reports = [];

    /** @var list<EndpointType> */
    private array $runs = [];

    /**
     * Make `run($type)` return a report built from these results.
     */
    public function returns(EndpointType $type, CheckResult ...$results): self
    {
        return $this->returnsReport(HealthReport::fromResults($type, array_values($results)));
    }

    public function returnsReport(HealthReport $report): self
    {
        $this->reports[$report->type->value] = $report;

        return $this;
    }

    public function run(EndpointType $type): HealthReport
    {
        $this->runs[] = $type;

        return $this->reports[$type->value] ?? HealthReport::fromResults($type, []);
    }

    /**
     * @return list<EndpointType>
     */
    public function runs(): array
    {
        return $this->runs;
    }

    public function assertRan(EndpointType $type, ?int $times = null): void
    {
        $count = $this->countRuns($type);

        if ($times === null) {
            Assert::assertGreaterThan(0, $count, "Health checks for [{$type->value}] were never run.");

            return;
        }

        Assert::assertSame($times, $count, "Health checks for [{$type->value}] ran {$count} time(s), expected {$times}.");
    }

    public function assertNotRan(EndpointType $type): void
    {
        $count = $this->countRuns($type);

        Assert::assertSame(0, $count, "Health checks for [{$type->value}] ran {$count} time(s), expected none.");
    }

    private function countRuns(EndpointType $type): int
    {
        return count(array_filter($this->runs, fn (EndpointType $run): bool => $run === $type));
    }
}
