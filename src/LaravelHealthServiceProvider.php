<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth;

use Cbox\LaravelHealth\Commands\HealthCheckCommand;
use Cbox\LaravelHealth\Commands\ScheduleHeartbeatCommand;
use Cbox\LaravelHealth\Config\HealthConfig;
use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Cbox\LaravelHealth\Services\PrometheusRenderer;
use Cbox\LaravelHealth\Services\SystemMetricsService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class LaravelHealthServiceProvider extends ServiceProvider
{
    private const string CONFIG = __DIR__.'/../config/health.php';

    private const string VIEWS = __DIR__.'/../resources/views';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG, 'health');

        $this->app->singleton(HealthConfig::class, fn (): HealthConfig => HealthConfig::fromConfig());

        $this->app->singleton(HealthCheckRunner::class);

        // Consumers depend on the contract; rebind it to decorate or replace the runner.
        $this->app->singleton(
            RunsHealthChecks::class,
            fn (Application $app): RunsHealthChecks => $app->make(HealthCheckRunner::class),
        );

        $this->app->singleton(SystemMetricsService::class);

        $this->app->singleton(
            PrometheusRenderer::class,
            fn (Application $app): PrometheusRenderer => new PrometheusRenderer($app->make(HealthConfig::class)->prometheusNamespace),
        );

        $this->app->singleton(LaravelHealth::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/health.php');
        $this->loadViewsFrom(self::VIEWS, 'health');

        $this->commands([
            HealthCheckCommand::class,
            ScheduleHeartbeatCommand::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->publishes([self::CONFIG => config_path('health.php')], 'health-config');
            $this->publishes([self::VIEWS => resource_path('views/vendor/health')], 'health-views');
        }
    }
}
