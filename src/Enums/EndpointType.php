<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Enums;

enum EndpointType: string
{
    case Liveness = 'liveness';
    case Readiness = 'readiness';
    case Startup = 'startup';
    case Status = 'status';
}
