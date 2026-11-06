<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app->make('url')->forceRootUrl(getenv('SLIDEWIRE_URL') ?: 'http://127.0.0.1:8017');
echo Illuminate\Support\Facades\Blade::render(file_get_contents(__DIR__.'/darkwood-original.blade.php'));
