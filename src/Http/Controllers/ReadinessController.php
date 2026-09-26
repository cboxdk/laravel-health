<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Http\Controllers;

use Cbox\LaravelHealth\Contracts\RunsHealthChecks;
use Cbox\LaravelHealth\Enums\EndpointType;
use Illuminate\Http\JsonResponse;

final class ReadinessController
{
    public function __invoke(RunsHealthChecks $runner): JsonResponse
    {
        $report = $runner->run(EndpointType::Readiness);

        return new JsonResponse(
            $report->toArray(),
            $report->isPassing() ? 200 : 503,
        );
    }
}
