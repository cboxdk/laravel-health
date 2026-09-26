<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Exceptions;

use Throwable;

/**
 * Marker for every exception thrown by this package, so a host can catch them in one place.
 */
interface HealthException extends Throwable {}
