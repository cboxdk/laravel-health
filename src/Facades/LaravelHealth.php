<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool check(\Illuminate\Http\Request $request)
 * @method static \Cbox\LaravelHealth\LaravelHealth auth(\Closure(\Illuminate\Http\Request): bool $callback)
 *
 * @see \Cbox\LaravelHealth\LaravelHealth
 */
class LaravelHealth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Cbox\LaravelHealth\LaravelHealth::class;
    }
}
