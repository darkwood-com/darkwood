<?php

declare(strict_types=1);

namespace App\Tools;

use function Castor\import;

import(__DIR__ . '/env/castor.php');
import(__DIR__ . '/php-cs-fixer/castor.php');
import(__DIR__ . '/phpstan/castor.php');
import(__DIR__ . '/phpunit/castor.php');

#[\Castor\Attribute\AsTask(name: 'ci-fixtures', namespace: '')]
function ciFixtures(): void
{
    $configuration = simplexml_load_file(__DIR__.'/phpunit/phpunit.xml');
    $environment = ['APP_RUNTIME_OPTIONS' => '{"disable_dotenv":true}'];

    foreach ($configuration->php->env as $entry) {
        $environment[(string) $entry['name']] = (string) $entry['value'];
    }

    $context = \Castor\context()->withEnvironment($environment);
    \Castor\run(['php', 'bin/console', '--env=test', 'doctrine:database:create', '--if-not-exists'], $context);
    \Castor\run(['php', 'bin/console', '--env=test', 'doctrine:schema:update', '--force'], $context);
}
