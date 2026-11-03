<?php

namespace App\Command;

use App\Account\AccountOperationException;
use App\Account\AccountService;
use App\Terminal\ExitCode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'accounts:disable', description: 'Disable monitoring for an account')]
final class AccountsDisableCommand extends Command
{
    public function __construct(private readonly AccountService $accounts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Local account identifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $account = $this->accounts->setMonitoring((string) $input->getArgument('id'), false);
            $defaults = $this->accounts->defaultsFor($account);
        } catch (AccountOperationException $error) {
            $io->error($error->getMessage());

            return $error->exitCode->value;
        }
        $io->success(sprintf('Monitoring is disabled for "%s".', $account->id));
        if ($defaults['provider'] || $defaults['product']) {
            $io->warning('This disabled account remains assigned as a default.');
        }

        return ExitCode::Success->value;
    }
}
