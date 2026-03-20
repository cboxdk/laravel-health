<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Commands;

use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Illuminate\Console\Command;

final class HealthCheckCommand extends Command
{
    protected $signature = 'health:check {--endpoint= : Run checks for a specific endpoint (liveness, readiness, startup)}';

    protected $description = 'Run health checks and display results';

    public function handle(HealthCheckRunner $runner): int
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
                    $this->formatStatus($result->status->value) . $message,
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
        /** @var string|null $endpoint */
        $endpoint = $this->option('endpoint');

        if ($endpoint !== null) {
            $type = EndpointType::tryFrom($endpoint);

            if ($type === null) {
                $valid = implode(', ', array_map(fn (EndpointType $t): string => $t->value, EndpointType::cases()));
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
