<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\StorageCheck;
use Cbox\LaravelHealth\Enums\Status;

it('passes when storage is writable', function (): void {
    config()->set('health.checks_config.storage.disk', 'local');

    $check = new StorageCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok)
        ->and($result->name)->toBe('storage');
});
