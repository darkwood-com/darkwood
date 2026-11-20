<?php

declare(strict_types=1);

namespace App\Tools;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\run;

#[AsTask(description: 'Instant Upgrades and Automated Refactoring', aliases: ['rector'])]
function rector(
    #[AsOption(description: 'Apply the proposed changes')]
    bool $apply,
): int {
    $command = [__DIR__ . '/vendor/bin/rector', 'process', '--config', __DIR__ . '/rector.php'];
    if (!$apply) {
        $command[] = '--dry-run';
    }

    return run(
        $command,
        context()->withAllowFailure()->withTty(true),
    )->getExitCode();
}
