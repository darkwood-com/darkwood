<?php

declare(strict_types=1);

namespace App\Tools;

use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(description: 'Start dev server', aliases: ['env-dev'])]
function envDev(): int
{
    return run(
        ['nix --extra-experimental-features "nix-command flakes" develop .'],
        tty: true,
        allowFailure: true,
    )->getExitCode();
}
