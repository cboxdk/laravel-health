<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Commands;

use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\Enums\EndpointType;
use Illuminate\Console\Command;

final class HealthCheckCommand extends Command
{
    protected $signature = 'health:check {--endpoint= : Run checks for a specific endpoint (liveness, readiness, startup)}';

    protected $description = 'Run health checks and display results';

    public function handle(RunsHealthChecks $runner): int
    {
        $endpoints = $this->getEndpoints();

        if ($endpoints === []) {
            return self::FAILURE;
        }

        $hasFailure = false;

        foreach ($endpoints as $endpoint) {
            $report = $runner->run($endpoint);

            $this->components->twoColumnDetail(
                "<fg=white;options=bold>{$endpoint->value}</>",
                $this->formatStatus($report->status->value),
            );

            foreach ($report->results as $result) {
                $message = $result->message !== '' && $result->message !== 'OK'
                    ? " <fg=gray>{$result->message}</>"
                    : '';

                $this->components->twoColumnDetail(
                    "  {$result->name}",
                    $this->formatStatus($result->status->value).$message,
                );
            }

            if (! $report->isPassing()) {
                $hasFailure = true;
            }
        }

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return EndpointType[]
     */
    private function getEndpoints(): array
    {
        $endpoint = $this->option('endpoint');

        if (is_string($endpoint)) {
            $probes = [EndpointType::Liveness, EndpointType::Readiness, EndpointType::Startup];
            $type = EndpointType::tryFrom($endpoint);

            if ($type === null || ! in_array($type, $probes, true)) {
                $valid = implode(', ', array_map(fn (EndpointType $t): string => $t->value, $probes));
                $this->components->error("Invalid endpoint '{$endpoint}'. Valid options: {$valid}");

                return [];
            }

            return [$type];
        }

        return [EndpointType::Liveness, EndpointType::Readiness];
    }

    private function formatStatus(string $status): string
    {
        return match ($status) {
            'ok' => '<fg=green>OK</>',
            'warning' => '<fg=yellow>WARNING</>',
            'critical' => '<fg=red>CRITICAL</>',
            default => '<fg=gray>UNKNOWN</>',
        };
    }
}
