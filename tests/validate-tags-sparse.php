<?php

declare(strict_types=1);

$fixture = sys_get_temp_dir().'/catalog-sparse-'.bin2hex(random_bytes(8));
mkdir($fixture.'/library/kept', 0700, true);
mkdir($fixture.'/library/omitted', 0700, true);
file_put_contents($fixture.'/library/kept/README.md', 'kept');
file_put_contents($fixture.'/library/omitted/README.md', 'omitted');
copy(__DIR__.'/../tags.schema.json', $fixture.'/tags.schema.json');
$project = static fn (string $id): array => [
    'id' => $id,
    'path' => 'library/'.$id,
    'domain' => 'darkwood',
    'description' => $id.' fixture',
    'kind' => 'other',
    'status' => 'done',
    'dependencies' => [],
    'topics' => ['test'],
];
file_put_contents($fixture.'/tags.json', json_encode([
    'schemaVersion' => 3,
    'repository' => 'fixture',
    'projects' => [$project('kept'), $project('omitted')],
], JSON_THROW_ON_ERROR));
file_put_contents($fixture.'/card-profiles.json', json_encode([
    'schemaVersion' => 1,
    'profiles' => [
        'fixture' => ['mode' => 'sparse', 'description' => 'fixture', 'cards' => ['kept'], 'keepPaths' => []],
    ],
], JSON_THROW_ON_ERROR));
$run = static function (string $command): int {
    exec($command.' >/dev/null 2>&1', $output, $code);

    return $code;
};
$git = 'git -C '.escapeshellarg($fixture).' ';
$validate = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../tools/validate-tags.php').' '.escapeshellarg($fixture);
try {
    foreach (['init -q', 'add .', '-c user.name=Test -c user.email=test@example.invalid -c commit.gpgsign=false commit -qm fixture', 'sparse-checkout set library/kept'] as $args) {
        if (0 !== $run($git.$args)) {
            throw new RuntimeException('Fixture setup failed.');
        }
    }
    if (is_dir($fixture.'/library/omitted') || 0 !== $run($validate)) {
        throw new RuntimeException('Sparse tracked project was rejected.');
    }
    unlink($fixture.'/library/kept/README.md');
    rmdir($fixture.'/library/kept');
    if (0 === $run($validate)) {
        throw new RuntimeException('Missing selected project was accepted.');
    }
    echo "Catalog validation: sparse omission accepted; deleted selected project rejected.\n";
} finally {
    $remove = function (string $file) use (&$remove): void {
        if (is_dir($file) && !is_link($file)) {
            foreach (array_diff(scandir($file) ?: [], ['.', '..']) as $entry) {
                $remove($file.'/'.$entry);
            }
            rmdir($file);
        } else {
            unlink($file);
        }
    };
    $remove($fixture);
}
