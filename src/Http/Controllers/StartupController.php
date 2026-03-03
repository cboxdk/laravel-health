<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Http\Controllers;

use Cbox\LaravelHealth\Enums\EndpointType;
use Cbox\LaravelHealth\Services\HealthCheckRunner;
use Illuminate\Http\JsonResponse;

final class StartupController
{
    public function __invoke(HealthCheckRunner $runner): JsonResponse
    {
        $report = $runner->run(EndpointType::Startup);

        return new JsonResponse(
            $report->toArray(),
            $report->isPassing() ? 200 : 503,
        );
    }
}
