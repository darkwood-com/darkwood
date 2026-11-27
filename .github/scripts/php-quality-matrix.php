<?php

declare(strict_types=1);

chdir(dirname(__DIR__, 2));

/** @return array<string, mixed> */
function readJson(string $path): array
{
    try {
        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    } catch (Throwable $exception) {
        throw new RuntimeException("$path: {$exception->getMessage()}", previous: $exception);
    }
}

$manifest = readJson('.github/distribution-cards.json');
$mirrors = array_column(array_filter($manifest['cards'], static fn (array $entry): bool => isset($entry['git']['composer'])), null, 'source');
$files = array_fill_keys(explode("\0", (string) shell_exec('git ls-files -z -- library')), true);
$paths = array_filter(array_keys($files), static fn (string $file): bool => str_ends_with($file, '/composer.json'));
$projects = [];

foreach (array_filter($paths) as $path) {
    $dir = dirname($path);
    if (preg_match('~/(?:vendor|node_modules|tools|var|cache|dist|build|public|fixtures)(?:/|$)~i', $dir)) {
        continue;
    }

    $composer = readJson($path);
    $card = explode('/', $dir)[1];
    $mirror = $mirrors[$dir]['git'] ?? [];
    $requirements = ($composer['require'] ?? []) + ($composer['require-dev'] ?? []);
    $constraint = $requirements['php'] ?? '>=8.5';
    if (!isset($mirror['phpVersion']) && !preg_match('/^(?:\^|>=|~)?(\d+\.\d+)(?:\.\d+)?$/D', $constraint, $minimum)) {
        throw new RuntimeException("$path: select a CI PHP version for constraint $constraint.");
    }
    $php = $mirror['phpVersion'] ?? $minimum[1];
    if (preg_match('/^(?:>=|\^8\.)/', $constraint) && version_compare($php, '8.5', '<')) {
        $php = '8.5';
    }

    $hasCastor = isset($files["$dir/castor.php"]) || isset($files["$dir/tools/castor.php"]);
    $tools = [];
    foreach (['php-cs-fixer', 'phpstan', 'phpunit'] as $tool) {
        $tools[$tool] = isset($files["$dir/tools/$tool/composer.json"]);
        if ($tools[$tool] && !$hasCastor) {
            throw new RuntimeException("$dir: $tool has a tools manifest but the Castor entry point is missing.");
        }
    }
    $tools['php-cs-fixer'] = $tools['php-cs-fixer'] || isset($requirements['friendsofphp/php-cs-fixer']) || isset($requirements['php-cs-fixer/shim']);
    $tools['phpstan'] = $tools['phpstan'] || isset($requirements['phpstan/phpstan']);
    $tools['phpunit'] = $tools['phpunit'] || isset($files["$dir/phpunit.xml"])
        || isset($files["$dir/phpunit.xml.dist"]) || isset($files["$dir/phpunit.dist.xml"]) || isset($requirements['phpunit/phpunit']);
    if ($tools['phpunit'] && !$hasCastor && !isset($requirements['phpunit/phpunit']) && !isset($requirements['symfony/phpunit-bridge'])) {
        throw new RuntimeException("$dir: PHPUnit configuration exists but the tool is missing from Composer.");
    }

    $runtimeBootBlocked = in_array($card, [
        'content-composio-mcp', 'content-hearthbreaker', 'content-joplin-api',
        'content-recherche-entreprise-mcp', 'content-thumbnail-mcp', 'content-url-scrapping-api',
    ], true);
    $hasDotenv = isset($files["$dir/.env"]);

    $entry = [
        'card' => $card,
        'package' => $composer['name'] ?? '',
        'directory' => $dir,
        'php' => $php,
        'extensions' => implode(', ', array_unique(array_filter(array_merge(
            array_map(static fn (string $key): string => substr($key, 4), array_filter(array_keys($requirements), static fn (string $key): bool => str_starts_with($key, 'ext-'))),
            explode(', ', $mirror['phpExtensions'] ?? ''),
        )))),
        ...$tools,
        'prepare-dotenv' => $tools['phpunit'] && !$hasDotenv && ($dir === 'library/slide' || isset($files["$dir/tests/bootstrap.php"])),
        // Bootstrapping deployed applications is separate from installing their CI dependencies.
        'composer-scripts' => !isset($composer['scripts']['auto-scripts']) || ($hasDotenv && !$runtimeBootBlocked),
        'mysql' => $mirror['mysql'] ?? in_array($card, ['darkwood-portal', 'uniflow'], true),
        'prepare-task' => in_array($dir, ['library/darkwood-portal', 'library/slide'], true) ? 'ci-fixtures' : '',
        'symfony-lsp' => ($mirror['packageId'] ?? '') === 'media-bundle'
            || (($mirror['packageId'] ?? '') === 'navi' && isset($files["$dir/bin/console"])),
        'nix-file' => '',
        'nix-args' => '[]',
    ];

    switch ($mirror['packageId'] ?? null) {
        case 'flow':
            $entry['nix-args'] = json_encode(['--argstr', 'naviEnv', '%project%/../Navi/tools/env', '--arg', 'withOpenSwoole', 'true'], JSON_THROW_ON_ERROR);
            break;
        case 'navi':
            $entry['nix-args'] = json_encode(['--argstr', 'lockFile', '%project%/tools/env/flake.lock', '--arg', 'withQualityTools', 'true', '--arg', 'extraExtensions', '{ all, ... }: [ all.openswoole ]'], JSON_THROW_ON_ERROR);
            break;
    }
    if ($entry['nix-args'] !== '[]') {
        $entry['nix-file'] = 'tools/env/shell.nix';
        // Preserve the existing exception for the PHP 8.6 parser until the fixer supports it.
        $entry['php-cs-fixer'] = false;
    }

    $projects[$dir] = $entry;
}

foreach ($mirrors as $dir => $mirror) {
    if (($projects[$dir]['package'] ?? null) !== $mirror['git']['composer']) {
        throw new RuntimeException("$dir: PHP distribution package is absent or does not match its Composer manifest.");
    }
}

echo json_encode(['include' => array_values($projects)], JSON_THROW_ON_ERROR), "\n";
