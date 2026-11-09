<?php

/*
 * Given modified file paths, return the list of monorepo packages to test.
 *
 * Usage:
 *   php .github/get-modified-packages.php all
 *   php .github/get-modified-packages.php "$(git diff --name-only ...)"
 */

declare(strict_types=1);

if (!is_file($manifestFile = __DIR__.'/packages.json')) {
    fwrite(STDERR, "Missing packages manifest: {$manifestFile}\n");
    exit(1);
}

$manifest = json_decode((string) file_get_contents($manifestFile), true, 512, JSON_THROW_ON_ERROR);
$packages = $manifest['packages'] ?? [];

if ([] === $packages) {
    echo '[]';
    exit(0);
}

$modifiedInput = $argv[1] ?? 'all';

if ('-' === $modifiedInput) {
    $modifiedInput = stream_get_contents(STDIN) ?: '';
}

if ('all' === $modifiedInput) {
    echo json_encode($packages, JSON_THROW_ON_ERROR);
    exit(0);
}

$modifiedFiles = array_values(array_filter(explode("\n", str_replace("\r\n", "\n", $modifiedInput))));

if ([] === $modifiedFiles) {
    echo '[]';
    exit(0);
}

$runAll = false;
foreach ($modifiedFiles as $file) {
    if (str_starts_with($file, '.github/')) {
        $runAll = true;
        break;
    }
}

if ($runAll) {
    echo json_encode($packages, JSON_THROW_ON_ERROR);
    exit(0);
}

usort($packages, static fn (array $a, array $b): int => strlen($b['directory']) <=> strlen($a['directory']));

$selected = [];
foreach ($modifiedFiles as $file) {
    foreach ($packages as $package) {
        $directory = $package['directory'];
        if ($file === $directory || str_starts_with($file, $directory.'/')) {
            $selected[$package['id']] = $package;
            break;
        }
    }
}

echo json_encode(array_values($selected), JSON_THROW_ON_ERROR);
