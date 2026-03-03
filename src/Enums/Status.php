<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Enums;

enum Status: string
{
    case Ok = 'ok';
    case Warning = 'warning';
    case Critical = 'critical';
    case Unknown = 'unknown';

    public function isHealthy(): bool
    {
        return $this === self::Ok || $this === self::Warning;
    }

    public function isPassing(): bool
    {
        return $this === self::Ok;
    }

    /**
     * Determine the worst status from a list.
     *
     * @param  Status[]  $statuses
     */
    public static function worst(array $statuses): self
    {
        $priority = [
            self::Critical->value => 3,
            self::Unknown->value => 2,
            self::Warning->value => 1,
            self::Ok->value => 0,
        ];

        $worst = self::Ok;

        foreach ($statuses as $status) {
            if ($priority[$status->value] > $priority[$worst->value]) {
                $worst = $status;
            }
        }

        return $worst;
    }

    public function numericValue(): float
    {
        return match ($this) {
            self::Ok => 1.0,
            self::Warning => 0.5,
            self::Critical => 0.0,
            self::Unknown => 0.0,
        };
    }
}
