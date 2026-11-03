<?php

namespace App\Command;

use App\Account\AccountOperationException;
use App\Account\AccountService;
use App\Account\AuthenticationMethod;
use App\Provider\ProviderCatalog;
use App\Terminal\ExitCode;
use App\Terminal\JsonOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'accounts:edit', description: 'Edit local account metadata')]
final class AccountsEditCommand extends Command
{
    public function __construct(
        private readonly AccountService $accounts,
        private readonly ProviderCatalog $catalog,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Local account identifier')
            ->addOption('alias', null, InputOption::VALUE_REQUIRED, 'New display alias')
            ->addOption('clear-alias', null, InputOption::VALUE_NONE, 'Remove the display alias')
            ->addOption('auth-method', null, InputOption::VALUE_REQUIRED, 'New authentication metadata method')
            ->addOption('auth-reference', null, InputOption::VALUE_REQUIRED, 'New non-secret authentication reference')
            ->addOption('clear-auth-reference', null, InputOption::VALUE_NONE, 'Remove the authentication reference')
            ->addOption('monitor', null, InputOption::VALUE_NEGATABLE, 'Enable or disable monitoring')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table or json', 'table')
            ->setHelp('Provider and local ID are immutable. Authentication changes reset verification to not checked.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        if (!\in_array($format, ['table', 'json'], true)) {
            $io->error('Format must be "table" or "json".');

            return ExitCode::InvalidInput->value;
        }
        if ($input->getOption('clear-alias') && null !== $input->getOption('alias')) {
            $io->error('Options --alias and --clear-alias cannot be combined.');

            return ExitCode::InvalidInput->value;
        }
        if ($input->getOption('clear-auth-reference') && null !== $input->getOption('auth-reference')) {
            $io->error('Options --auth-reference and --clear-auth-reference cannot be combined.');

            return ExitCode::InvalidInput->value;
        }

        try {
            $current = $this->accounts->get((string) $input->getArgument('id'));
            if ($input->isInteractive()) {
                $alias = (string) $io->ask('Display alias (empty removes it)', $current->alias ?? '');
                $method = (string) $io->choice(
                    'Authentication metadata',
                    array_map(static fn (AuthenticationMethod $method): string => $method->value, $this->catalog->authenticationMethods($current->providerKey)),
                    $current->authenticationMethod->value,
                );
                $reference = $current->authenticationReference;
                if (AuthenticationMethod::None->value === $method) {
                    $reference = null;
                } elseif ($method !== $current->authenticationMethod->value || $io->confirm('Replace the configured authentication reference?', false)) {
                    $reference = $io->ask('New non-secret authentication reference');
                }
                $monitoring = $io->confirm('Enable monitoring?', $current->monitoringEnabled);
            } else {
                $hasChange = null !== $input->getOption('alias')
                    || $input->getOption('clear-alias')
                    || null !== $input->getOption('auth-method')
                    || null !== $input->getOption('auth-reference')
                    || $input->getOption('clear-auth-reference')
                    || null !== $input->getOption('monitor');
                if (!$hasChange) {
                    throw new AccountOperationException('At least one edit option is required in non-interactive mode.', ExitCode::InvalidInput);
                }
                $alias = $input->getOption('clear-alias') ? null : ($input->getOption('alias') ?? $current->alias);
                $method = (string) ($input->getOption('auth-method') ?? $current->authenticationMethod->value);
                $reference = $input->getOption('clear-auth-reference') ? null : ($input->getOption('auth-reference') ?? $current->authenticationReference);
                if (AuthenticationMethod::None->value === $method) {
                    $reference = null;
                }
                $monitoring = null === $input->getOption('monitor') ? $current->monitoringEnabled : (bool) $input->getOption('monitor');
            }

            $account = $this->accounts->edit($current->id, null === $alias ? null : (string) $alias, $method, null === $reference ? null : (string) $reference, $monitoring);
        } catch (AccountOperationException $error) {
            $io->error($error->getMessage());

            return $error->exitCode->value;
        }

        if ('json' === $format) {
            JsonOutput::write($output, ['account' => $account->toSafeArray(true)]);
        } else {
            $io->success(sprintf('Account "%s" was updated.', $account->id));
            $io->note('Changing authentication metadata never verifies it. The provider remains unchanged.');
        }

        return ExitCode::Success->value;
    }
}
