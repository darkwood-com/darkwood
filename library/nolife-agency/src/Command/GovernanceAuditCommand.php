<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'nolife:governance:audit', description: 'Inspect audit logs for a run.')]
final class GovernanceAuditCommand extends Command
{
    public function __construct(
        private readonly string $auditDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('run-id', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runId = (string) $input->getOption('run-id');
        if ($runId === '') {
            $output->writeln('<error>--run-id is required.</error>');

            return Command::INVALID;
        }

        $path = rtrim($this->auditDir, '/').'/'.$runId.'.jsonl';
        if (!is_file($path)) {
            $output->writeln(sprintf('<error>No audit file found for run "%s".</error>', $runId));

            return Command::FAILURE;
        }

        $output->writeln(file_get_contents($path) ?: '');

        return Command::SUCCESS;
    }
}
