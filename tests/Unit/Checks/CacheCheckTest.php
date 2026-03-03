<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\CacheCheck;
use Cbox\LaravelHealth\Enums\Status;

it('passes when cache is working', function (): void {
    $check = new CacheCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok)
        ->and($result->name)->toBe('cache');
});
