<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Checks;

use Cbox\LaravelHealth\DataTransferObjects\CheckResult;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class StorageCheck extends BaseCheck
{
    public function run(): CheckResult
    {
        try {
            /** @var string $disk */
            $disk = config('health.checks_config.storage.disk', 'local');

            $path = 'health_check_'.bin2hex(random_bytes(8)).'.txt';

            Storage::disk($disk)->put($path, 'health_check');
            Storage::disk($disk)->delete($path);

            return CheckResult::ok($this->name());
        } catch (Throwable $e) {
            return CheckResult::critical($this->name(), $e->getMessage());
        }
    }
}
