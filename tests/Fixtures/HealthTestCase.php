<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Tests\Fixtures;

use Cbox\LaravelHealth\Testing\InteractsWithHealth;

/**
 * Composes the package's testing trait so PHPStan analyses it (traits are only
 * analysed where they are used).
 */
final class HealthTestCase
{
    use InteractsWithHealth;
}
