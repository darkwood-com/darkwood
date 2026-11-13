<?php

declare(strict_types=1);

$sourceRoot = dirname(__DIR__);
$fixture = sys_get_temp_dir().'/darkwood-cards-cli-'.bin2hex(random_bytes(8));

$remove = static function (string $path) use (&$remove): void {
    if (is_dir($path) && !is_link($path)) {
        foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $entry) {
            $remove($path.'/'.$entry);
        }
        rmdir($path);
    } elseif (file_exists($path) || is_link($path)) {
        unlink($path);
    }
};
$run = static function (array $command, ?string $cwd = null): array {
    $pipes = [];
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to run command.');
    }
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    return ['code' => proc_close($process), 'out' => $out, 'err' => $err];
};
$must = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

try {
    mkdir($fixture.'/tools', 0700, true);
    mkdir($fixture.'/bin', 0700, true);
    mkdir($fixture.'/library/a', 0700, true);
    mkdir($fixture.'/library/b', 0700, true);
    copy($sourceRoot.'/tools/cards.php', $fixture.'/tools/cards.php');
    copy($sourceRoot.'/bin/darkwood', $fixture.'/bin/darkwood');
    file_put_contents($fixture.'/library/a/README.md', "a\n");
    file_put_contents($fixture.'/library/b/README.md', "b\n");
    file_put_contents($fixture.'/.gitignore', "library/b/cache/\n");
    file_put_contents($fixture.'/tags.json', json_encode([
        'schemaVersion' => 3,
        'repository' => 'fixture',
        'projects' => [
            ['id' => 'a', 'path' => 'library/a', 'kind' => 'product', 'status' => 'working', 'dependencies' => []],
            ['id' => 'b', 'path' => 'library/b', 'kind' => 'tool', 'status' => 'done', 'dependencies' => ['a']],
        ],
    ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    file_put_contents($fixture.'/card-profiles.json', json_encode([
        'schemaVersion' => 1,
        'profiles' => [
            'test' => ['mode' => 'sparse', 'description' => 'fixture', 'cards' => ['a'], 'keepPaths' => ['tools', 'bin']],
        ],
    ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

    foreach ([
        ['git', 'init', '-q'],
        ['git', 'add', '.'],
        ['git', '-c', 'user.name=Test', '-c', 'user.email=test@example.invalid', '-c', 'commit.gpgsign=false', 'commit', '-qm', 'fixture'],
        ['git', 'sparse-checkout', 'set', 'tools', 'bin', 'library/a'],
    ] as $command) {
        $result = $run($command, $fixture);
        $must(0 === $result['code'], 'Fixture setup failed: '.$result['err']);
    }

    $status = $run([PHP_BINARY, $fixture.'/bin/darkwood', 'cards', 'status'], sys_get_temp_dir());
    $must(0 === $status['code'] && str_contains($status['out'], '1 loaded, 1 unloaded'), 'Initial physical status is wrong.');

    $load = $run([PHP_BINARY, 'bin/darkwood', 'cards', 'load', 'b'], $fixture);
    $must(0 === $load['code'] && is_file($fixture.'/library/b/README.md'), 'Card load failed.');

    mkdir($fixture.'/library/b/cache', 0700, true);
    file_put_contents($fixture.'/library/b/cache/local.txt', 'unique');
    $blocked = $run([PHP_BINARY, 'bin/darkwood', 'cards', 'unload', 'b'], $fixture);
    $must(0 !== $blocked['code'] && str_contains($blocked['err'], 'ignored files'), 'Ignored data did not block unload.');
    unlink($fixture.'/library/b/cache/local.txt');
    rmdir($fixture.'/library/b/cache');

    $unload = $run([PHP_BINARY, 'bin/darkwood', 'cards', 'unload', 'b'], $fixture);
    $must(0 === $unload['code'] && !is_dir($fixture.'/library/b'), 'Reversible unload failed.');

    $reload = $run([PHP_BINARY, 'bin/darkwood', 'cards', 'load', 'b'], $fixture);
    $must(0 === $reload['code'] && is_file($fixture.'/library/b/README.md'), 'Reload after unload failed.');

    $profile = $run([PHP_BINARY, 'bin/darkwood', 'cards', 'profile', 'apply', 'test'], $fixture);
    $must(0 === $profile['code'], 'Profile apply failed: '.$profile['err']);
    $protected = $run([PHP_BINARY, 'bin/darkwood', 'cards', 'unload', 'a'], $fixture);
    $must(0 !== $protected['code'] && str_contains($protected['err'], 'required by active profile'), 'Profile did not protect its card.');

    fwrite(STDOUT, "cards CLI: list/status, load, guarded unload, reload and profile protection verified\n");
} finally {
    if (is_dir($fixture)) {
        $remove($fixture);
    }
}

