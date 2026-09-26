<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Tests;

use Cbox\LaravelHealth\LaravelHealthServiceProvider;
use Cbox\LaravelHealth\Testing\InteractsWithHealth;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use InteractsWithHealth;

    protected function getPackageProviders($app): array
    {
        return [
            LaravelHealthServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
