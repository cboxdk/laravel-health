<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Contracts;

use Cbox\LaravelHealth\DataTransferObjects\HealthReport;
use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Testing\FakeHealthCheckRunner;

/**
 * Runs the health checks configured for an endpoint and aggregates them into a report.
 *
 * Every endpoint, the dashboard and the `health:check` command resolve this contract,
 * so a host can decorate or replace the runner, and tests can swap in
 * {@see FakeHealthCheckRunner}.
 */
interface RunsHealthChecks
{
    public function run(EndpointType $type): HealthReport;
}
