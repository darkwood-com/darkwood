<?php

declare(strict_types=1);

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\run;

#[AsTask(name: 'ci-fixtures')]
function buildTestAssets(): void
{
    $project = context()->withWorkingDirectory(dirname(__DIR__));
    run(['npm', 'ci', '--no-audit', '--no-fund'], $project);
    run(['npm', 'run', 'build'], $project);
}

#[AsTask(name: 'phpunit')]
function phpunit(): int
{
    return run(['composer', 'run-script', 'test'], context()->withWorkingDirectory(dirname(__DIR__))->withAllowFailure())->getExitCode();
}
