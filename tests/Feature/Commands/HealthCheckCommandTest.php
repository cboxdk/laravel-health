<?php

declare(strict_types=1);

it('exits with 0 when all checks pass', function (): void {
    config()->set('health.checks.liveness', []);
    config()->set('health.checks.readiness', []);
    config()->set('health.cache.enabled', false);

    $this->artisan('health:check')
        ->assertExitCode(0);
});

it('exits with 1 when checks fail', function (): void {
    $failingCheck = new class implements \Cbox\LaravelHealth\Contracts\HealthCheck
    {
        public function name(): string
        {
            return 'failing';
        }

        public function run(): \Cbox\LaravelHealth\DataTransferObjects\CheckResult
        {
            return \Cbox\LaravelHealth\DataTransferObjects\CheckResult::critical('failing', 'Service down');
        }
    };

    config()->set('health.checks.readiness', [$failingCheck::class]);
    config()->set('health.checks.liveness', []);
    config()->set('health.cache.enabled', false);
    app()->bind($failingCheck::class, fn () => $failingCheck);

    $this->artisan('health:check')
        ->assertExitCode(1);
});

it('supports endpoint filter option', function (): void {
    config()->set('health.checks.liveness', []);
    config()->set('health.cache.enabled', false);

    $this->artisan('health:check', ['--endpoint' => 'liveness'])
        ->assertExitCode(0);
});
