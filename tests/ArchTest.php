<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->each->not->toBeUsed();

arch('strict types everywhere')
    ->expect('Cbox\LaravelHealth')
    ->toUseStrictTypes();

arch('enums are enums')
    ->expect('Cbox\LaravelHealth\Enums')
    ->toBeEnums();

arch('contracts are interfaces')
    ->expect('Cbox\LaravelHealth\Contracts')
    ->toBeInterfaces();

arch('DTOs are readonly')
    ->expect('Cbox\LaravelHealth\DataTransferObjects')
    ->toBeReadonly();

arch('checks implement HealthCheck contract')
    ->expect('Cbox\LaravelHealth\Checks')
    ->toImplement('Cbox\LaravelHealth\Contracts\HealthCheck');

arch('every package exception carries the HealthException marker')
    ->expect('Cbox\LaravelHealth\Exceptions')
    ->classes()
    ->toImplement('Cbox\LaravelHealth\Exceptions\HealthException');

arch('consumers depend on the runner contract, not the implementation')
    ->expect(['Cbox\LaravelHealth\Http', 'Cbox\LaravelHealth\Commands', 'Cbox\LaravelHealth\Testing'])
    ->not->toUse('Cbox\LaravelHealth\Services\HealthCheckRunner');

arch('final by default')
    ->expect('Cbox\LaravelHealth')
    ->classes()
    ->toBeFinal()
    ->ignoring([
        // Abstract base that host applications extend for their own checks.
        'Cbox\LaravelHealth\Checks\BaseCheck',
        // Host packages may extend the provider to adjust registration.
        'Cbox\LaravelHealth\LaravelHealthServiceProvider',
        // Mocked by tests (here and in host apps) to feed fixed system metrics;
        // Mockery cannot mock a final class.
        'Cbox\LaravelHealth\Services\SystemMetricsService',
        // Laravel facades are resolved and swapped by the framework, conventionally open.
        'Cbox\LaravelHealth\Facades\LaravelHealth',
    ]);
