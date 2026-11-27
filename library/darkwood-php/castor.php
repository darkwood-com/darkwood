<?php

declare(strict_types=1);

namespace Darkwood\Tools;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\run;

defined('CASTOR_USE_CHDIR') || define('CASTOR_USE_CHDIR', true);

#[AsTask(name: 'darkwood-theme', namespace: '', description: 'Build or verify the Darkwood Sass theme')]
function darkwoodTheme(#[AsOption(description: 'Compare the compiled CSS with the distributed files')] bool $check = false): void
{
    run([
        'npm',
        '--prefix',
        __DIR__ . '/src/Darkwood/Bundle/DarkwoodBundle',
        'run',
        $check ? 'check:css' : 'build:css',
    ]);
}
