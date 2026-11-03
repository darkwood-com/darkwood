<?php

namespace App\Command;

use App\Account\AccountOperationException;
use App\Account\AccountService;
use App\Subscription\SubscriptionOperationException;
use App\Subscription\SubscriptionService;
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
    private const SECTIONS = ['Providers', 'Accounts', 'Subscriptions', 'Products & Entitlements', 'Exit'];
    private const ACCOUNT_ACTIONS = ['List', 'Add', 'Inspect', 'Edit', 'Enable monitoring', 'Disable monitoring', 'Set or clear provider default', 'Remove', 'Back'];
    private const SUBSCRIPTION_ACTIONS = ['List', 'Add', 'Inspect', 'Edit', 'Remove', 'Back'];
    private const PRODUCT_ACTIONS = ['List entitlements', 'Add entitlement', 'Remove entitlement', 'Back'];

    public function __construct(private readonly AccountService $accounts, private readonly SubscriptionService $subscriptions)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Run bin/nolife without arguments for guided management. Explicit subcommands remain available for scripts.');
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
            $io->title('NOLIFE TRACKER');
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
            $section = $io->choice('Section', self::SECTIONS, 'Exit');
            if ('Exit' === $section) {
                $io->writeln('Goodbye.');
                return ExitCode::Success->value;
            }
            $command = $this->sectionCommand((string) $section, $io);
            if (null === $command) {
                continue;
            }
            [$name, $arguments] = $command;
            $commandInput = new ArrayInput($arguments);
            $commandInput->setInteractive(true);
            $this->getApplication()?->find($name)->run($commandInput, $output);
            $io->newLine();
        }
    }

    /** @return array{string, array<string, string|bool>}|null */
    private function sectionCommand(string $section, SymfonyStyle $io): ?array
    {
        try {
            return match ($section) {
                'Providers' => ['providers:list', []],
                'Accounts' => $this->accountCommand((string) $io->choice('Account action', self::ACCOUNT_ACTIONS, 'Back'), $io),
                'Subscriptions' => $this->subscriptionCommand((string) $io->choice('Subscription action', self::SUBSCRIPTION_ACTIONS, 'Back'), $io),
                'Products & Entitlements' => $this->productCommand((string) $io->choice('Entitlement action', self::PRODUCT_ACTIONS, 'Back'), $io),
                default => null,
            };
        } catch (AccountOperationException|SubscriptionOperationException $error) {
            $io->error($error->getMessage());
            return null;
        }
    }

    /** @return array{string, array<string, string|bool>}|null */
    private function accountCommand(string $action, SymfonyStyle $io): ?array
    {
        return match ($action) {
            'List' => ['accounts:list', []],
            'Add' => ['accounts:add', []],
            'Inspect' => ['accounts:show', ['id' => $this->accountId($io)]],
            'Edit' => ['accounts:edit', ['id' => $this->accountId($io)]],
            'Enable monitoring' => ['accounts:enable', ['id' => $this->accountId($io)]],
            'Disable monitoring' => ['accounts:disable', ['id' => $this->accountId($io)]],
            'Set or clear provider default' => ['accounts:default', ['id' => $this->accountId($io)]],
            'Remove' => ['accounts:remove', ['id' => $this->accountId($io)]],
            default => null,
        };
    }

    /** @return array{string, array<string, string|bool>}|null */
    private function subscriptionCommand(string $action, SymfonyStyle $io): ?array
    {
        return match ($action) {
            'List' => ['subscriptions:list', []],
            'Add' => ['subscriptions:add', []],
            'Inspect' => ['subscriptions:show', ['id' => $this->subscriptionId($io)]],
            'Edit' => ['subscriptions:edit', ['id' => $this->subscriptionId($io)]],
            'Remove' => ['subscriptions:remove', ['id' => $this->subscriptionId($io)]],
            default => null,
        };
    }

    /** @return array{string, array<string, string|bool>}|null */
    private function productCommand(string $action, SymfonyStyle $io): ?array
    {
        return match ($action) {
            'List entitlements' => ['subscriptions:products', ['id' => $this->subscriptionId($io)]],
            'Add entitlement' => ['subscriptions:products:add', ['id' => $this->subscriptionId($io)]],
            'Remove entitlement' => ['subscriptions:products:remove', ['id' => $this->subscriptionId($io)]],
            default => null,
        };
    }

    private function accountId(SymfonyStyle $io): string
    {
        $accounts = $this->accounts->all();
        if ([] === $accounts) {
            throw new AccountOperationException('Register an account first.', ExitCode::InvalidInput);
        }
        return (string) $io->choice('Account', array_map(static fn ($account): string => $account->id, $accounts));
    }

    private function subscriptionId(SymfonyStyle $io): string
    {
        $subscriptions = $this->subscriptions->all();
        if ([] === $subscriptions) {
            throw new SubscriptionOperationException('Add a subscription first.', ExitCode::InvalidInput);
        }
        return (string) $io->choice('Subscription', array_map(static fn ($subscription): string => $subscription->id, $subscriptions));
    }
}
