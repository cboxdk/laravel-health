<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Enums\EndpointType;

it('has correct values', function (): void {
    expect(EndpointType::Liveness->value)->toBe('liveness')
        ->and(EndpointType::Readiness->value)->toBe('readiness')
        ->and(EndpointType::Startup->value)->toBe('startup')
        ->and(EndpointType::Status->value)->toBe('status');
});

it('can be created from string', function (): void {
    expect(EndpointType::from('liveness'))->toBe(EndpointType::Liveness)
        ->and(EndpointType::from('readiness'))->toBe(EndpointType::Readiness);
});
