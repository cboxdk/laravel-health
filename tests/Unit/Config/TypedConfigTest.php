<?php

declare(strict_types=1);

use Cbox\LaravelHealth\Config\TypedConfig;

it('reads strings and falls back to the default', function (): void {
    config()->set('health.test.value', 'custom');

    expect(TypedConfig::string('health.test.value', 'default'))->toBe('custom')
        ->and(TypedConfig::string('health.test.missing', 'default'))->toBe('default');
});

it('reads nullable strings', function (): void {
    config()->set('health.test.value', null);

    expect(TypedConfig::nullableString('health.test.value'))->toBeNull();

    config()->set('health.test.value', 'redis');

    expect(TypedConfig::nullableString('health.test.value'))->toBe('redis');
});

it('accepts booleans and boolean-like env strings', function (mixed $value, bool $expected): void {
    config()->set('health.test.value', $value);

    expect(TypedConfig::boolean('health.test.value', ! $expected))->toBe($expected);
})->with([
    [true, true],
    [false, false],
    ['true', true],
    ['false', false],
    ['1', true],
    ['0', false],
    [1, true],
    [0, false],
]);

it('accepts integers and integer strings', function (): void {
    config()->set('health.test.value', '30');

    expect(TypedConfig::integer('health.test.value', 10))->toBe(30);

    config()->set('health.test.value', 15);

    expect(TypedConfig::integer('health.test.value', 10))->toBe(15);
});

it('accepts numbers and numeric strings', function (): void {
    config()->set('health.test.value', 85);
    expect(TypedConfig::number('health.test.value', 90))->toBe(85);

    config()->set('health.test.value', 1.5);
    expect(TypedConfig::number('health.test.value', 2.0))->toBe(1.5);

    config()->set('health.test.value', '2.5');
    expect(TypedConfig::number('health.test.value', 2.0))->toBe(2.5);
});

it('reads string lists and treats a single string as a list', function (): void {
    config()->set('health.test.value', ['a', 'b']);
    expect(TypedConfig::stringList('health.test.value'))->toBe(['a', 'b']);

    config()->set('health.test.value', 'api');
    expect(TypedConfig::stringList('health.test.value'))->toBe(['api']);

    expect(TypedConfig::stringList('health.test.missing', ['web']))->toBe(['web']);
});

it('reads nullable string lists', function (): void {
    config()->set('health.test.value', null);
    expect(TypedConfig::nullableStringList('health.test.value'))->toBeNull();

    config()->set('health.test.value', ['10.0.0.0/8']);
    expect(TypedConfig::nullableStringList('health.test.value'))->toBe(['10.0.0.0/8']);
});

it('rejects values of the wrong type', function (Closure $read, mixed $value): void {
    config()->set('health.test.value', $value);

    expect($read)->toThrow(InvalidArgumentException::class, 'Configuration value [health.test.value]');
})->with([
    'string' => [fn () => TypedConfig::string('health.test.value', ''), ['array']],
    'nullable string' => [fn () => TypedConfig::nullableString('health.test.value'), 42],
    'boolean' => [fn () => TypedConfig::boolean('health.test.value', true), 'maybe'],
    'integer' => [fn () => TypedConfig::integer('health.test.value', 1), '1.5'],
    'number' => [fn () => TypedConfig::number('health.test.value', 1), 'ninety'],
    'string list' => [fn () => TypedConfig::stringList('health.test.value'), ['a', 1]],
    'string list scalar' => [fn () => TypedConfig::stringList('health.test.value'), 42],
]);
