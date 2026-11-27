<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Blade;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$app->make('url')->forceRootUrl(getenv('SLIDEWIRE_URL') ?: 'http://127.0.0.1:8017');
echo Blade::render(file_get_contents(__DIR__.'/design-system.blade.php'));
