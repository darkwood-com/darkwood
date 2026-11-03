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

#[AsCommand(name: 'accounts:remove', description: 'Remove an account from local tracking')]
final class AccountsRemoveCommand extends Command
{
    public function __construct(private readonly AccountService $accounts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Local account identifier')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Remove without interactive confirmation')
            ->setHelp('Only the Nolife Tracker record and its default assignments are removed. External authentication is untouched.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = (string) $input->getArgument('id');
        try {
            $this->accounts->get($id);
            if (!$input->getOption('force')) {
                if (!$input->isInteractive()) {
                    throw new AccountOperationException('The --force option is required in non-interactive mode.', ExitCode::InvalidInput);
                }
                if (!$io->confirm(sprintf('Remove only the local record "%s" and its default assignments?', $id), false)) {
                    $io->note('No account was removed.');

                    return ExitCode::Success->value;
                }
            }
            $account = $this->accounts->remove($id);
        } catch (AccountOperationException $error) {
            $io->error($error->getMessage());

            return $error->exitCode->value;
        }

        $io->success(sprintf('Account "%s" was removed from Nolife Tracker.', $account->id));
        $io->note('No provider credential, CLI profile, or external account was changed.');

        return ExitCode::Success->value;
    }
}
