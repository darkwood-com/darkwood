<?php

namespace App\Command;

use App\Account\AccountOperationException;
use App\Account\AccountService;
use App\Terminal\ExitCode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'accounts:default', description: 'Set or clear a provider default account')]
final class AccountsDefaultCommand extends Command
{
    public function __construct(private readonly AccountService $accounts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Local account identifier identifying the provider')
            ->addOption('scope', null, InputOption::VALUE_REQUIRED, 'Deprecated compatibility option; only provider is accepted')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear the selected default instead of assigning this account');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $scope = $input->getOption('scope');
        if (null !== $scope && 'provider' !== $scope) {
            $io->error('Product defaults were removed in v0.1.1; defaults now apply only to provider accounts.');

            return ExitCode::InvalidInput->value;
        }

        try {
            $account = $this->accounts->setDefault((string) $input->getArgument('id'), (bool) $input->getOption('clear'));
        } catch (AccountOperationException $error) {
            $io->error($error->getMessage());

            return $error->exitCode->value;
        }
        $io->success($input->getOption('clear')
            ? sprintf('The provider default identified by account "%s" was cleared.', $account->id)
            : sprintf('Account "%s" is now the provider default.', $account->id));

        return ExitCode::Success->value;
    }
}
