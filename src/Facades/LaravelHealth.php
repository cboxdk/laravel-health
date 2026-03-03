<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cbox\LaravelHealth\LaravelHealth
 */
class LaravelHealth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Cbox\LaravelHealth\LaravelHealth::class;
    }
}
