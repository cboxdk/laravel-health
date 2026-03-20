<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth;

use Cbox\LaravelHealth\Commands\HealthCheckCommand;
use Cbox\LaravelHealth\Commands\ScheduleHeartbeatCommand;
use Cbox\LaravelHealth\Config\HealthConfig;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Cbox\LaravelHealth\Services\PrometheusRenderer;
use Cbox\LaravelHealth\Services\SystemMetricsService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LaravelHealthServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('health')
            ->hasConfigFile('health')
            ->hasRoute('health')
            ->hasViews('health')
            ->hasCommands([
                HealthCheckCommand::class,
                ScheduleHeartbeatCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(HealthConfig::class, function () {
            return HealthConfig::fromConfig();
        });

        $this->app->singleton(HealthCheckRunner::class);

        $this->app->singleton(SystemMetricsService::class);

        $this->app->singleton(PrometheusRenderer::class, function ($app) {
            /** @var HealthConfig $config */
            $config = $app->make(HealthConfig::class);

            return new PrometheusRenderer($config->prometheusNamespace);
        });

        $this->app->singleton(LaravelHealth::class);

        $this->app->bind('health.auth', function ($app) {
            return $app->make(LaravelHealth::class);
        });
    }
}
