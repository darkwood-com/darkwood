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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'accounts:add', description: 'Register a local AI account')]
final class AccountsAddCommand extends Command
{
    public function __construct(
        private readonly ProviderCatalog $catalog,
        private readonly AccountService $accounts,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Unique local account identifier')
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Provider identifier')
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'Deprecated; products are managed through subscriptions')
            ->addOption('alias', null, InputOption::VALUE_REQUIRED, 'Display alias')
            ->addOption('auth-method', null, InputOption::VALUE_REQUIRED, 'Authentication metadata method')
            ->addOption('auth-reference', null, InputOption::VALUE_REQUIRED, 'Non-secret profile, environment variable, or credential-store locator')
            ->addOption('monitor', null, InputOption::VALUE_NEGATABLE, 'Enable or disable monitoring')
            ->addOption('default', null, InputOption::VALUE_REQUIRED, 'Set as provider default: none or provider', 'none')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table or json', 'table')
            ->setHelp('Registers local metadata only. It never verifies authentication and never accepts passwords, tokens, or cookies.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        if (!\in_array($format, ['table', 'json'], true)) {
            $io->error('Format must be "table" or "json".');

            return ExitCode::InvalidInput->value;
        }

        try {
            $default = (string) $input->getOption('default');
            if (null !== $input->getOption('product')) {
                throw new AccountOperationException('The --product option was removed in v0.1.1; associate products through subscriptions:products:add.', ExitCode::InvalidInput);
            }
            if (!\in_array($default, ['none', 'provider'], true)) {
                throw new AccountOperationException('Default must be "none" or "provider".', ExitCode::InvalidInput);
            }
            $values = $this->values($input, $io);
            $account = $this->accounts->add(...$values);
            if ('none' !== $default) {
                $this->accounts->setDefault($account->id);
            }
        } catch (AccountOperationException $error) {
            $io->error($error->getMessage());

            return $error->exitCode->value;
        }

        if ('json' === $format) {
            JsonOutput::write($output, ['account' => $account->toSafeArray(true)]);
        } else {
            $io->success(sprintf('Account "%s" was registered locally.', $account->id));
            $io->definitionList(
                ['Provider' => $account->providerKey],
                ['Authentication' => $account->authenticationVerificationState->label()],
                ['Monitoring' => $account->monitoringEnabled ? 'enabled' : 'disabled'],
            );
            $io->note('Registration does not mean the account is authenticated. No provider was contacted.');
        }

        return ExitCode::Success->value;
    }

    /**
     * @return array{id: string, providerKey: string, alias: ?string, authenticationMethod: string, authenticationReference: ?string, monitoringEnabled: bool}
     */
    private function values(InputInterface $input, SymfonyStyle $io): array
    {
        $interactive = $input->isInteractive();
        $providerKey = $this->required($input->getOption('provider'), 'The --provider option is required in non-interactive mode.', $interactive
            ? fn (): string => (string) $io->choice('Provider', array_map(static fn ($provider): string => $provider->key, $this->catalog->all()))
            : null);
        $provider = $this->catalog->find($providerKey);
        if (null === $provider) {
            throw new AccountOperationException(sprintf('Provider "%s" was not found.', $providerKey), ExitCode::NotFound);
        }

        $id = $this->required($input->getOption('id'), 'The --id option is required in non-interactive mode.', $interactive
            ? fn (): string => (string) $io->ask('Unique local account ID')
            : null);
        $method = $this->required($input->getOption('auth-method'), 'The --auth-method option is required in non-interactive mode.', $interactive
            ? fn (): string => (string) $io->choice('Authentication metadata', array_map(static fn (AuthenticationMethod $method): string => $method->value, $this->catalog->authenticationMethods($providerKey)), AuthenticationMethod::None->value)
            : null);
        $alias = $input->getOption('alias');
        if ($interactive && null === $alias) {
            $alias = $io->ask('Display alias (optional)');
        }
        $reference = $input->getOption('auth-reference');
        if ($interactive && AuthenticationMethod::None->value !== $method && null === $reference) {
            $reference = $io->ask('Non-secret authentication reference');
        }
        $monitoring = $input->getOption('monitor');
        if (null === $monitoring) {
            $monitoring = $interactive ? $io->confirm('Enable monitoring?', true) : true;
        }

        return [
            'id' => $id,
            'providerKey' => $providerKey,
            'alias' => null === $alias ? null : (string) $alias,
            'authenticationMethod' => $method,
            'authenticationReference' => null === $reference ? null : (string) $reference,
            'monitoringEnabled' => (bool) $monitoring,
        ];
    }

    private function required(mixed $value, string $message, ?callable $prompt): string
    {
        if (null !== $value && '' !== trim((string) $value)) {
            return trim((string) $value);
        }
        if (null !== $prompt) {
            return $prompt();
        }

        throw new AccountOperationException($message, ExitCode::InvalidInput);
    }
}
