<?php

namespace App\Command;

use App\Account\AccountOperationException;
use App\Account\AccountService;
use App\Terminal\AccountTable;
use App\Terminal\ExitCode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'nolife', description: 'Open the interactive Nolife Tracker menu')]
final class HomeCommand extends Command
{
    private const ACTIONS = [
        'List accounts',
        'Add account',
        'Inspect account',
        'Edit account',
        'Enable monitoring',
        'Disable monitoring',
        'Set or clear a default',
        'Remove account',
        'List providers',
        'Quit',
    ];

    public function __construct(private readonly AccountService $accounts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Run bin/nolife without arguments for guided account management. Explicit subcommands remain available for scripts.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (!$input->isInteractive()) {
            $io->title('Nolife Tracker');
            $io->text('Interactive input is disabled. Use "bin/nolife list" to discover scriptable commands.');

            return ExitCode::Success->value;
        }

        while (true) {
            $io->title('Nolife Tracker');
            try {
                $accounts = $this->accounts->all();
                if ([] === $accounts) {
                    $io->note('No accounts are registered locally yet.');
                } else {
                    AccountTable::render($io, $accounts);
                }
            } catch (AccountOperationException $error) {
                $io->error($error->getMessage());

                return $error->exitCode->value;
            }

            $action = $io->choice('Action', self::ACTIONS, 'Quit');
            if ('Quit' === $action) {
                $io->writeln('Goodbye.');

                return ExitCode::Success->value;
            }

            try {
                [$command, $arguments] = $this->commandFor((string) $action, $io);
            } catch (AccountOperationException $error) {
                $io->error($error->getMessage());
                $io->newLine();

                continue;
            }
            $commandInput = new ArrayInput($arguments);
            $commandInput->setInteractive(true);
            $this->getApplication()?->find($command)->run($commandInput, $output);
            $io->newLine();
        }
    }

    /**
     * @return array{string, array<string, string|bool>}
     */
    private function commandFor(string $action, SymfonyStyle $io): array
    {
        return match ($action) {
            'List accounts' => ['accounts:list', []],
            'Add account' => ['accounts:add', []],
            'Inspect account' => ['accounts:show', ['id' => $this->accountId($io)]],
            'Edit account' => ['accounts:edit', ['id' => $this->accountId($io)]],
            'Enable monitoring' => ['accounts:enable', ['id' => $this->accountId($io)]],
            'Disable monitoring' => ['accounts:disable', ['id' => $this->accountId($io)]],
            'Set or clear a default' => ['accounts:default', ['id' => $this->accountId($io)]],
            'Remove account' => ['accounts:remove', ['id' => $this->accountId($io)]],
            'List providers' => ['providers:list', []],
            default => throw new \LogicException(sprintf('Unsupported menu action "%s".', $action)),
        };
    }

    private function accountId(SymfonyStyle $io): string
    {
        $accounts = $this->accounts->all();
        if ([] === $accounts) {
            throw new AccountOperationException('Register an account first.', ExitCode::InvalidInput);
        }

        return (string) $io->choice(
            'Account',
            array_map(static fn ($account): string => $account->id, $accounts),
        );
    }
}
