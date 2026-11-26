<?php

declare(strict_types=1);

namespace App\Tools;

use Castor\Attribute\AsTask;

use function Castor\capture;
use function Castor\context;
use function Castor\import;
use function Castor\run;

import(__DIR__ . '/env/castor.php');
import(__DIR__ . '/php-cs-fixer/castor.php');
import(__DIR__ . '/phpstan/castor.php');
import(__DIR__ . '/phpunit/castor.php');

#[AsTask(name: 'ci-fixtures', namespace: '')]
function ciFixtures(): void
{
    $variables = capture(['php', '-r', <<<'PHP'
        $configuration = simplexml_load_file($argv[1]);
        $environment = [];
        foreach ($configuration->php->env as $entry) {
            $environment[(string) $entry['name']] = (string) $entry['value'];
        }
        echo json_encode($environment, JSON_THROW_ON_ERROR);
        PHP,
        __DIR__.'/phpunit/phpunit.xml',
    ]);
    $environment = json_decode($variables, true, flags: JSON_THROW_ON_ERROR);
    $environment['APP_RUNTIME_OPTIONS'] = '{"disable_dotenv":true}';

    $context = context()->withEnvironment($environment);
    run(['php', dirname(__DIR__).'/bin/console', '--env=test', 'doctrine:database:create', '--if-not-exists'], $context);
    run(['php', dirname(__DIR__).'/bin/console', '--env=test', 'doctrine:schema:update', '--force'], $context);
}
