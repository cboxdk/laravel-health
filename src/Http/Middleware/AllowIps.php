<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Http\Middleware;

use Cbox\LaravelHealth\Config\HealthConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AllowIps
{
    public function __construct(
        private readonly HealthConfig $config,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = $this->config->allowedIps();

        // If no IP restriction configured, pass through
        if ($allowedIps === null) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if ($clientIp !== null && in_array($clientIp, $allowedIps, true)) {
            return $next($request);
        }

        abort(403, 'IP not allowed');
    }
}
