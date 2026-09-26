---
title: Custom Checks
description: Create custom health checks by implementing the HealthCheck contract.
weight: 61
---

# Custom Checks

Create custom health checks by implementing the `HealthCheck` contract.

## The Contract

```php
namespace Cbox\LaravelHealth\Contracts;

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;

interface HealthCheck
{
    public function name(): string;

    public function run(): CheckResult;
}
```

## Example: API Dependency Check

```php
<?php

namespace App\Health;

use Cbox\LaravelHealth\Contracts\HealthCheck;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Illuminate\Support\Facades\Http;

class PaymentGatewayCheck implements HealthCheck
{
    public function name(): string
    {
        return 'payment_gateway';
    }

    public function run(): CheckResult
    {
        try {
            $response = Http::timeout(5)->get('https://payments.example.com/health');

            if ($response->successful()) {
                return CheckResult::ok($this->name());
            }

            return CheckResult::critical(
                $this->name(),
                "HTTP {$response->status()}",
            );
        } catch (\Throwable $e) {
            return CheckResult::critical($this->name(), $e->getMessage());
        }
    }
}
```

## Using the Base Class

Extend `BaseCheck` to get automatic name generation from the class name (the `Check` suffix is dropped and the rest is converted to snake case):

```php
<?php

namespace App\Health;

use Cbox\LaravelHealth\Checks\BaseCheck;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;

class PaymentGatewayCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        // name() automatically returns 'payment_gateway'
        // ...
    }
}
```

## Dependencies

Checks are built through the service container, so you can type-hint dependencies in the constructor:

```php
use Illuminate\Contracts\Cache\Repository;

class FeatureFlagCheck extends BaseCheck
{
    public function __construct(private readonly Repository $cache) {}

    public function run(): CheckResult
    {
        // ...
    }
}
```

## Registering Custom Checks

> **Warning**: Never put external service checks on the **liveness** probe. If the external service goes down, Kubernetes will restart your pods in a loop — cascading a dependency failure into a full outage. External checks belong on **readiness**. See [Kubernetes Probes](../endpoints/kubernetes-probes.md#cascading-failures).

Add your check class to the appropriate endpoint in `config/health.php`:

```php
use App\Health\PaymentGatewayCheck;

'checks' => [
    'readiness' => [           // readiness, not liveness
        DatabaseCheck::class,
        CacheCheck::class,
        PaymentGatewayCheck::class,
    ],
],
```

## Errors

You don't need to catch everything. If `run()` throws, the runner turns the exception into a `critical` result named after the check class, with the exception message. The same happens when a configured class doesn't exist, can't be built by the container, or doesn't implement `HealthCheck`. Catching exceptions yourself (as in the example above) keeps your check's own name in the report.

The runner measures each check's duration and sets it on the result, so you don't need to.

## CheckResult API

```php
CheckResult::ok($name, $message = 'OK', $metadata = []);
CheckResult::warning($name, $message = '', $metadata = []);
CheckResult::critical($name, $message = '', $metadata = []);
CheckResult::unknown($name, $message = '', $metadata = []);
```

`warning` keeps the probe passing (`200`); `critical` and `unknown` fail it (`503`).

The `$metadata` array is included in the probe and status JSON responses, useful for exposing diagnostic data like queue sizes or response times.

If two checks on the same endpoint return the same name, the JSON output keys the second one as `<name>_2`, the third `<name>_3`, and so on.

## Related Documentation

- [Health Checks Overview](../health-checks/_index.md)
- [Configuration Reference](../configuration/reference.md)
- [Replacing the Runner](replacing-the-runner.md)
