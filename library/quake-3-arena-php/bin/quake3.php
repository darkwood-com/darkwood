#!/usr/bin/env php
<?php

declare(strict_types=1);

use Quake3\Console\Application;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Run composer install in {$root} first.\n");
    exit(1);
}

require $autoload;

ini_set('max_execution_time', '0');
ini_set('memory_limit', '1024M');

// SDL/OpenGL commands need FFI; enable when not preload-restricted
if (extension_loaded('ffi') && (ini_get('ffi.enable') === '0' || ini_get('ffi.enable') === 'false')) {
    // cannot enable at runtime if disabled; user must pass -d ffi.enable=true
}

exit((new Application())->run($argv));
