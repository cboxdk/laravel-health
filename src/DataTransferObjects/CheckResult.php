<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\DataTransferObjects;

use Cbox\LaravelHealth\Enums\Status;

final readonly class CheckResult
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $name,
        public Status $status,
        public string $message = '',
        public float $durationMs = 0.0,
        public array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function ok(string $name, string $message = 'OK', array $metadata = []): self
    {
        return new self(
            name: $name,
            status: Status::Ok,
            message: $message,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function warning(string $name, string $message = '', array $metadata = []): self
    {
        return new self(
            name: $name,
            status: Status::Warning,
            message: $message,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function critical(string $name, string $message = '', array $metadata = []): self
    {
        return new self(
            name: $name,
            status: Status::Critical,
            message: $message,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function unknown(string $name, string $message = '', array $metadata = []): self
    {
        return new self(
            name: $name,
            status: Status::Unknown,
            message: $message,
            metadata: $metadata,
        );
    }

    public function withDuration(float $durationMs): self
    {
        return new self(
            name: $this->name,
            status: $this->status,
            message: $this->message,
            durationMs: $durationMs,
            metadata: $this->metadata,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'status' => $this->status->value,
            'message' => $this->message,
            'duration_ms' => round($this->durationMs, 2),
            'metadata' => $this->metadata,
        ];
    }
}
