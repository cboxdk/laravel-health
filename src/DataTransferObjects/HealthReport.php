<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\DataTransferObjects;

use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Enums\Status;
use DateTimeImmutable;

final readonly class HealthReport
{
    /**
     * @param  CheckResult[]  $results
     */
    public function __construct(
        public EndpointType $type,
        public Status $status,
        public array $results,
        public float $totalDurationMs,
        public DateTimeImmutable $checkedAt,
    ) {}

    public function isPassing(): bool
    {
        return $this->status->isHealthy();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $checks = [];
        foreach ($this->results as $result) {
            $checks[$result->name] = $result->toArray();
        }

        return [
            'status' => $this->status->value,
            'type' => $this->type->value,
            'checks' => $checks,
            'total_duration_ms' => round($this->totalDurationMs, 2),
            'checked_at' => $this->checkedAt->format('c'),
        ];
    }
}
