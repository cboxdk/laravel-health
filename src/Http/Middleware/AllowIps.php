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

        if ($clientIp !== null && self::ipMatches($clientIp, $allowedIps)) {
            return $next($request);
        }

        abort(403, 'IP not allowed');
    }

    /**
     * Check if an IP matches any entry in the allowlist (exact or CIDR).
     *
     * @param  array<int, string>  $allowedIps
     */
    public static function ipMatches(string $ip, array $allowedIps): bool
    {
        foreach ($allowedIps as $allowed) {
            if (str_contains($allowed, '/')) {
                if (self::cidrMatch($ip, $allowed)) {
                    return true;
                }
            } elseif ($ip === $allowed) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP falls within a CIDR range.
     */
    private static function cidrMatch(string $ip, string $cidr): bool
    {
        $parts = explode('/', $cidr, 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$subnet, $prefixLength] = $parts;

        if (! ctype_digit($prefixLength)) {
            return false;
        }

        $prefixLength = (int) $prefixLength;
        if ($prefixLength < 0 || $prefixLength > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = $prefixLength === 0 ? 0 : (~0 << (32 - $prefixLength));

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
