<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\QueueCheck;
use Cbox\LaravelHealth\Enums\Status;
use Illuminate\Support\Facades\Queue;

it('passes when queue is accessible', function (): void {
    $check = new QueueCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok)
        ->and($result->name)->toBe('queue')
        ->and($result->metadata)->toHaveKey('queue_size');
});

it('returns critical when queue connection fails', function (): void {
    Queue::shouldReceive('connection')
        ->andThrow(new RuntimeException('Queue connection refused'));

    $check = new QueueCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Critical)
        ->and($result->message)->toContain('Queue connection refused');
});

it('uses configured connection', function (): void {
    config()->set('health.checks_config.queue.connection', 'sync');

    $check = new QueueCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok);
});

it('derives name correctly', function (): void {
    $check = new QueueCheck;

    expect($check->name())->toBe('queue');
});
