<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Checks\EnvironmentCheck;
use Cbox\LaravelHealth\Enums\Status;

it('passes when no required vars configured', function (): void {
    config()->set('health.checks_config.environment.required', []);

    $check = new EnvironmentCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok);
});

it('passes when all required vars are set', function (): void {
    // Set a var we know exists via putenv to ensure it's available
    putenv('HEALTH_TEST_VAR=1');
    config()->set('health.checks_config.environment.required', ['HEALTH_TEST_VAR']);

    $check = new EnvironmentCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Ok);

    putenv('HEALTH_TEST_VAR');
});

it('fails when required vars are missing', function (): void {
    config()->set('health.checks_config.environment.required', ['TOTALLY_NONEXISTENT_VAR_12345']);

    $check = new EnvironmentCheck;
    $result = $check->run();

    expect($result->status)->toBe(Status::Critical)
        ->and($result->message)->toContain('TOTALLY_NONEXISTENT_VAR_12345');
});

it('derives name correctly', function (): void {
    $check = new EnvironmentCheck;

    expect($check->name())->toBe('environment');
});
