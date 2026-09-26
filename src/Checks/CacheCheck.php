<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\Config\TypedConfig;
use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

final class CacheCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        try {
            $store = TypedConfig::nullableString('health.checks_config.cache.store');

            $key = 'health_check_'.Str::random(16);
            $value = 'health_check_value';

            $cache = Cache::store($store);
            $cache->put($key, $value, 10);

            $retrieved = $cache->get($key);
            $cache->forget($key);

            if ($retrieved !== $value) {
                return CheckResult::critical($this->name(), 'Cache read/write verification failed');
            }

            return CheckResult::ok($this->name());
        } catch (Throwable $e) {
            return CheckResult::critical($this->name(), $e->getMessage());
        }
    }
}
