<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

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
