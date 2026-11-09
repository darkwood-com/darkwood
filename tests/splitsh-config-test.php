<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$config = json_decode((string) file_get_contents($root.'/splitsh.json'), true, 512, JSON_THROW_ON_ERROR);

if (false !== ($config['enabled'] ?? null)) {
    throw new RuntimeException('Splish must remain disabled.');
}
foreach ($config['publications'] ?? [] as $name => $publication) {
    $prefix = $publication['prefix'] ?? '';
    $remote = $publication['remote'] ?? '';
    if (!is_string($prefix) || !file_exists($root.'/'.$prefix)) {
        throw new RuntimeException($name.' has a missing public source prefix.');
    }
    if (!is_string($remote) || 1 !== preg_match('~^git@github\\.com:[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+\\.git$~', $remote)) {
        throw new RuntimeException($name.' must target a public GitHub repository.');
    }
}

fwrite(STDOUT, sprintf("Splish configuration: %d disabled public publications verified\n", count($config['publications'] ?? [])));
