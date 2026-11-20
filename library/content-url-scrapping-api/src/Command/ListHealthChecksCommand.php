<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('monitor:list', 'Lists Health Checks')]
final class ListHealthChecksCommand
{
    public function __invoke(OutputInterface $output): int
    {
        foreach (HealthCheckCommand::CHECKS as $id => $label) {
            $output->writeln($id.' '.$label);
        }

        return 0;
    }
}
