<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;

it('writes heartbeat to cache', function (): void {
    Cache::forget('health:schedule:heartbeat');

    $this->artisan('health:heartbeat')
        ->assertExitCode(0);

    expect(Cache::get('health:schedule:heartbeat'))->not->toBeNull();
});

it('overwrites existing heartbeat', function (): void {
    Cache::put('health:schedule:heartbeat', now()->subHour());

    $this->artisan('health:heartbeat')
        ->assertExitCode(0);

    $heartbeat = Cache::get('health:schedule:heartbeat');
    expect($heartbeat->diffInSeconds(now()))->toBeLessThan(5);
});
