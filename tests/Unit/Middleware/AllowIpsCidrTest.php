<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Http\Middleware\AllowIps;

it('matches exact IP addresses', function (): void {
    expect(AllowIps::ipMatches('10.0.0.1', ['10.0.0.1', '10.0.0.2']))->toBeTrue()
        ->and(AllowIps::ipMatches('10.0.0.3', ['10.0.0.1', '10.0.0.2']))->toBeFalse();
});

it('matches CIDR ranges', function (): void {
    expect(AllowIps::ipMatches('10.0.0.50', ['10.0.0.0/24']))->toBeTrue()
        ->and(AllowIps::ipMatches('10.0.1.1', ['10.0.0.0/24']))->toBeFalse()
        ->and(AllowIps::ipMatches('172.16.5.10', ['172.16.0.0/12']))->toBeTrue()
        ->and(AllowIps::ipMatches('192.168.1.1', ['172.16.0.0/12']))->toBeFalse();
});

it('handles mixed exact and CIDR entries', function (): void {
    $allowed = ['10.0.0.1', '192.168.0.0/16'];
    expect(AllowIps::ipMatches('10.0.0.1', $allowed))->toBeTrue()
        ->and(AllowIps::ipMatches('192.168.50.1', $allowed))->toBeTrue()
        ->and(AllowIps::ipMatches('10.0.0.2', $allowed))->toBeFalse();
});

it('returns false for empty allowlist', function (): void {
    expect(AllowIps::ipMatches('10.0.0.1', []))->toBeFalse();
});
