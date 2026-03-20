<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\RedisCheck;
use Cbox\LaravelHealth\Enums\Status;
use Illuminate\Support\Facades\Redis;

it('passes when redis returns true', function (): void {
    $connection = Mockery::mock();
    $connection->shouldReceive('command')->with('ping')->andReturn(true);
    Redis::shouldReceive('connection')->with('default')->andReturn($connection);

    $check = new RedisCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok)
        ->and($result->name)->toBe('redis');
});

it('passes when redis returns PONG string', function (): void {
    $connection = Mockery::mock();
    $connection->shouldReceive('command')->with('ping')->andReturn('PONG');
    Redis::shouldReceive('connection')->with('default')->andReturn($connection);

    $check = new RedisCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok);
});

it('passes when redis returns +PONG string', function (): void {
    $connection = Mockery::mock();
    $connection->shouldReceive('command')->with('ping')->andReturn('+PONG');
    Redis::shouldReceive('connection')->with('default')->andReturn($connection);

    $check = new RedisCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok);
});

it('returns critical for unexpected ping response', function (): void {
    $connection = Mockery::mock();
    $connection->shouldReceive('command')->with('ping')->andReturn('UNEXPECTED');
    Redis::shouldReceive('connection')->with('default')->andReturn($connection);

    $check = new RedisCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Critical)
        ->and($result->message)->toContain('Unexpected ping response');
});

it('returns critical when redis connection fails', function (): void {
    Redis::shouldReceive('connection')
        ->with('default')
        ->andThrow(new RuntimeException('Connection refused'));

    $check = new RedisCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Critical)
        ->and($result->message)->toContain('Connection refused');
});

it('uses configured connection name', function (): void {
    config()->set('health.checks_config.redis.connection', 'cache');

    $connection = Mockery::mock();
    $connection->shouldReceive('command')->with('ping')->andReturn(true);
    Redis::shouldReceive('connection')->with('cache')->andReturn($connection);

    $check = new RedisCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok);
});

it('derives name correctly', function (): void {
    $check = new RedisCheck;

    expect($check->name())->toBe('redis');
});
