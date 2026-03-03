<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\DatabaseCheck;
use Cbox\LaravelHealth\Enums\Status;

it('passes when database is reachable', function (): void {
    $check = new DatabaseCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok)
        ->and($result->name)->toBe('database');
});

it('derives name from class name', function (): void {
    $check = new DatabaseCheck;

    expect($check->name())->toBe('database');
});
