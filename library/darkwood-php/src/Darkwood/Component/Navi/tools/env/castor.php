<?php

declare(strict_types=1);

namespace App\Tools;

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\run;

#[AsTask(name: 'env', namespace: 'app:tools', description: 'Open the locked Navi PHP development shell', aliases: ['env-dev'])]
function envDev(): int
{
    return run(
        ['nix', '--extra-experimental-features', 'nix-command flakes', 'develop', '--file', __DIR__.'/shell.nix', '--arg', 'lockFile', json_encode(__DIR__.'/flake.lock', JSON_THROW_ON_ERROR)],
        context()->withAllowFailure()->withTty(true),
    )->getExitCode();
}
