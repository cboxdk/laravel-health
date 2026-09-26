#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Dependency license gate.
 *
 * Reads composer.lock (runtime AND dev packages) and fails when a package
 * does not declare at least one permissive license. Self-contained: no
 * composer plugins, no network, no vendor/ required.
 *
 * A package with multiple licenses passes if ANY of them is permissive
 * (composer.lock stores the license field as an array; multiple entries
 * follow SPDX dual-licensing OR semantics).
 *
 * Weak-copyleft licenses (e.g. MPL-2.0, LGPL) are intentionally NOT on the
 * allowlist — they require an explicit entry in $exceptions with a
 * justification.
 */
$allowlist = [
    'MIT',
    'BSD-2-Clause',
    'BSD-3-Clause',
    'BSD-4-Clause',
    'Apache-2.0',
    'ISC',
    '0BSD',
    'Unlicense',
    'CC0-1.0',
    'WTFPL',
];

/**
 * Explicitly excepted packages: 'vendor/pkg' => 'justification'.
 * Use this for weak-copyleft (MPL-2.0, LGPL-*) or unusual licenses after
 * review — never silently extend the allowlist.
 *
 * @var array<string, string> $exceptions
 */
$exceptions = [];

$root = dirname(__DIR__);
$lockFile = $root.'/composer.lock';

if (! is_file($lockFile)) {
    fwrite(STDERR, "composer.lock not found at {$lockFile}\n");
    exit(1);
}

/** @var array{packages?: list<array<string, mixed>>, "packages-dev"?: list<array<string, mixed>>} $lock */
$lock = json_decode((string) file_get_contents($lockFile), true, 512, JSON_THROW_ON_ERROR);

$packages = array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []);

$allowed = array_flip(array_map('strtoupper', $allowlist));
$offenders = [];
$excepted = [];
$checked = 0;

foreach ($packages as $package) {
    $name = (string) ($package['name'] ?? '');
    if ($name === '') {
        continue;
    }
    $checked++;

    /** @var list<string> $licenses */
    $licenses = array_map('strval', (array) ($package['license'] ?? []));

    $permissive = false;
    foreach ($licenses as $license) {
        if (isset($allowed[strtoupper($license)])) {
            $permissive = true;
            break;
        }
    }

    if ($permissive) {
        continue;
    }

    if (isset($exceptions[$name])) {
        $excepted[$name] = sprintf(
            '%s [%s] — exception: %s',
            $name,
            implode(' OR ', $licenses) ?: 'no license declared',
            $exceptions[$name],
        );

        continue;
    }

    $offenders[] = sprintf(
        '%s [%s]',
        $name,
        implode(' OR ', $licenses) ?: 'no license declared',
    );
}

foreach ($excepted as $line) {
    echo "EXCEPTION: {$line}\n";
}

if ($offenders !== []) {
    fwrite(STDERR, "Non-permissive licenses found:\n");
    foreach ($offenders as $offender) {
        fwrite(STDERR, "  - {$offender}\n");
    }
    fwrite(STDERR, sprintf(
        "\n%d offender(s) out of %d package(s) checked. Add an entry to \$exceptions in bin/check-licenses.php with a justification, or remove the dependency.\n",
        count($offenders),
        $checked,
    ));
    exit(1);
}

echo sprintf(
    "License check passed: %d packages checked (%d exception(s)).\n",
    $checked,
    count($excepted),
);
exit(0);
