<?php

declare(strict_types=1);

namespace Cbox\LaravelHealth\Config;

use Cbox\LaravelHealth\Exceptions\InvalidConfigurationException;

/**
 * Typed accessors for the package's config values.
 *
 * Config is host-controlled and often fed from env(), so values arrive as
 * mixed (and numbers/booleans frequently as strings). These accessors narrow
 * each value to the type the package needs and fail loudly on a
 * misconfiguration instead of silently casting it.
 *
 * @internal
 */
final class TypedConfig
{
    public static function string(string $key, string $default): string
    {
        $value = config($key, $default);

        if (is_string($value)) {
            return $value;
        }

        throw InvalidConfigurationException::wrongType($key, 'a string', $value);
    }

    public static function nullableString(string $key): ?string
    {
        $value = config($key);

        if ($value === null || is_string($value)) {
            return $value;
        }

        throw InvalidConfigurationException::wrongType($key, 'a string or null', $value);
    }

    public static function boolean(string $key, bool $default): bool
    {
        $value = config($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_string($value)) {
            $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($bool !== null) {
                return $bool;
            }
        }

        throw InvalidConfigurationException::wrongType($key, 'a boolean', $value);
    }

    public static function integer(string $key, int $default): int
    {
        $value = config($key, $default);

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value)) {
            $int = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if ($int !== null) {
                return $int;
            }
        }

        throw InvalidConfigurationException::wrongType($key, 'an integer', $value);
    }

    public static function number(string $key, int|float $default): int|float
    {
        $value = config($key, $default);

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return $value + 0;
        }

        throw InvalidConfigurationException::wrongType($key, 'a number', $value);
    }

    /**
     * A list of strings. A single string is treated as a one-item list.
     *
     * @param  list<string>  $default
     * @return list<string>
     */
    public static function stringList(string $key, array $default = []): array
    {
        $value = config($key, $default);

        if (is_string($value)) {
            return [$value];
        }

        if (! is_array($value)) {
            throw InvalidConfigurationException::wrongType($key, 'a list of strings', $value);
        }

        $list = [];

        foreach ($value as $item) {
            if (! is_string($item)) {
                throw InvalidConfigurationException::wrongType($key, 'a list of strings', $value);
            }

            $list[] = $item;
        }

        return $list;
    }

    /**
     * @return list<string>|null
     */
    public static function nullableStringList(string $key): ?array
    {
        return config($key) === null ? null : self::stringList($key);
    }
}
