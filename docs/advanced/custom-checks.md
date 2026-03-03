---
title: Custom Checks
description: Create custom health checks by implementing the HealthCheck contract.
weight: 41
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
            $response = Http::timeout(5)->get('https://api.stripe.com/v1/health');

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

Extend `BaseCheck` to get automatic name generation from the class name:

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

## CheckResult API

```php
CheckResult::ok($name, $message = 'OK', $metadata = []);
CheckResult::warning($name, $message = '', $metadata = []);
CheckResult::critical($name, $message = '', $metadata = []);
CheckResult::unknown($name, $message = '', $metadata = []);
```

The `$metadata` array is included in JSON and status responses, useful for exposing diagnostic data like queue sizes or response times.

## Related Documentation

- [Health Checks Overview](../health-checks/_index.md)
- [Configuration](../configuration.md)
