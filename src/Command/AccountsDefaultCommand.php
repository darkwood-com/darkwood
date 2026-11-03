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

#[AsCommand(name: 'accounts:default', description: 'Set or clear a provider/product default account')]
final class AccountsDefaultCommand extends Command
{
    public function __construct(private readonly AccountService $accounts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Local account identifier identifying the scope')
            ->addOption('scope', null, InputOption::VALUE_REQUIRED, 'Default scope: provider or product')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear the selected default instead of assigning this account');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $scope = $input->getOption('scope');
        if (null === $scope && $input->isInteractive()) {
            $scope = $io->choice('Default scope', ['provider', 'product']);
        }
        if (null === $scope) {
            $io->error('The --scope option is required in non-interactive mode.');

            return ExitCode::InvalidInput->value;
        }

        try {
            $account = $this->accounts->setDefault((string) $input->getArgument('id'), (string) $scope, (bool) $input->getOption('clear'));
        } catch (AccountOperationException $error) {
            $io->error($error->getMessage());

            return $error->exitCode->value;
        }
        $io->success($input->getOption('clear')
            ? sprintf('The %s default identified by account "%s" was cleared.', $scope, $account->id)
            : sprintf('Account "%s" is now the %s default.', $account->id, $scope));

        return ExitCode::Success->value;
    }
}
