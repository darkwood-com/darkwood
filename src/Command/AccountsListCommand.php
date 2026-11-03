<?php

namespace App\Command;

use App\Account\Account;
use App\Account\AccountOperationException;
use App\Account\AccountService;
use App\Terminal\AccountTable;
use App\Terminal\ExitCode;
use App\Terminal\JsonOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'accounts:list', description: 'List locally registered AI accounts', aliases: ['accounts'])]
final class AccountsListCommand extends Command
{
    public function __construct(private readonly AccountService $accounts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Filter by provider identifier')
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'Filter by product identifier (requires --provider)')
            ->addOption('enabled', null, InputOption::VALUE_NONE, 'Only monitoring-enabled accounts')
            ->addOption('disabled', null, InputOption::VALUE_NONE, 'Only monitoring-disabled accounts')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table or json', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        if (!\in_array($format, ['table', 'json'], true)) {
            $io->error('Format must be "table" or "json".');

            return ExitCode::InvalidInput->value;
        }
        if ($input->getOption('enabled') && $input->getOption('disabled')) {
            $io->error('Options --enabled and --disabled cannot be used together.');

            return ExitCode::InvalidInput->value;
        }
        $enabled = $input->getOption('enabled') ? true : ($input->getOption('disabled') ? false : null);

        try {
            $accounts = $this->accounts->all(
                null === $input->getOption('provider') ? null : (string) $input->getOption('provider'),
                null === $input->getOption('product') ? null : (string) $input->getOption('product'),
                $enabled,
            );
        } catch (AccountOperationException $error) {
            $io->error($error->getMessage());

            return $error->exitCode->value;
        }

        if ('json' === $format) {
            JsonOutput::write($output, ['accounts' => array_map(static fn (Account $account): array => $account->toSafeArray(), $accounts)]);

            return ExitCode::Success->value;
        }

        $io->title('Nolife Tracker accounts');
        if ([] === $accounts) {
            $io->note('No local accounts match the selected filters. Use accounts:add to register one.');

            return ExitCode::Success->value;
        }
        AccountTable::render($io, $accounts);
        $io->note('Registered means local metadata exists. Authentication is not verified by v0.1.');

        return ExitCode::Success->value;
    }
}
