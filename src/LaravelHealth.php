<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth;

use Closure;
use Illuminate\Http\Request;

class LaravelHealth
{
    /**
     * The callback that should be used to authenticate health check users.
     */
    public static ?Closure $authUsing = null;

    /**
     * Determine if the given request can access the health endpoints.
     */
    public function check(Request $request): bool
    {
        return (static::$authUsing ?? function (): bool {
            return app()->environment('local');
        })($request);
    }

    /**
     * Set the callback that should be used to authenticate health check users.
     */
    public static function auth(Closure $callback): self
    {
        static::$authUsing = $callback;

        return new self;
    }
}
