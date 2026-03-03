<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Http\Middleware;

use Cbox\LaravelHealth\Config\HealthConfig;
use Cbox\LaravelHealth\LaravelHealth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EndpointAuth
{
    public function __construct(
        private readonly HealthConfig $config,
        private readonly LaravelHealth $health,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $endpoint = ''): Response
    {
        // Public endpoints pass through
        if (in_array($endpoint, $this->config->publicEndpoints(), true)) {
            return $next($request);
        }

        // Check token authentication
        $configToken = $this->config->token();

        if ($configToken !== null && $configToken !== '') {
            /** @var string|null $requestToken */
            $requestToken = $request->query('token') ?? $request->bearerToken();

            if ($requestToken === $configToken) {
                return $next($request);
            }
        }

        // Fall back to auth callback
        if ($this->health->check($request)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
