<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\AppFactory;

$app = AppFactory::create(dirname(__DIR__));
$app->run();
