<?php

namespace App\Command;

use App\Account\AccountService;
use App\Subscription\BillingInterval;
use App\Subscription\BillingType;
use App\Subscription\SubscriptionOperationException;
use App\Subscription\SubscriptionService;
use App\Subscription\SubscriptionStatus;
use App\Terminal\ExitCode;
use App\Terminal\JsonOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'subscriptions:add', description: 'Add a subscription or billing arrangement to an account')]
final class SubscriptionsAddCommand extends Command
{
    public function __construct(private readonly SubscriptionService $subscriptions, private readonly AccountService $accounts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Stable local subscription identifier')
            ->addOption('account', null, InputOption::VALUE_REQUIRED, 'Account identifier')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Display name')
            ->addOption('plan', null, InputOption::VALUE_REQUIRED, 'Provider plan name or identifier')
            ->addOption('billing-type', null, InputOption::VALUE_REQUIRED, 'free, recurring, pay_as_you_go, prepaid, enterprise, or unknown', 'unknown')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'active, inactive, expired, canceled, or unknown', 'unknown')
            ->addOption('amount', null, InputOption::VALUE_REQUIRED, 'Billing amount in major currency units')
            ->addOption('currency', null, InputOption::VALUE_REQUIRED, 'Three-letter currency code')
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'day, week, month, or year')
            ->addOption('renews-at', null, InputOption::VALUE_REQUIRED, 'ISO 8601 renewal date')
            ->addOption('expires-at', null, InputOption::VALUE_REQUIRED, 'ISO 8601 expiration date')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table or json', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = (string) $input->getOption('format');
        if (!\in_array($format, ['table', 'json'], true)) {
            $io->error('Format must be "table" or "json".');
            return ExitCode::InvalidInput->value;
        }
        try {
            $values = $this->values($input, $io);
            $subscription = $this->subscriptions->add(...$values);
        } catch (SubscriptionOperationException $error) {
            $io->error($error->getMessage());
            return $error->exitCode->value;
        }
        if ('json' === $format) {
            JsonOutput::write($output, ['subscription' => $subscription->toArray(), 'products' => []]);
        } else {
            $io->success(sprintf('Subscription "%s" was added.', $subscription->id));
            $io->note('No product access was inferred. Add entitlements explicitly with subscriptions:products:add.');
        }
        return ExitCode::Success->value;
    }

    /** @return array<string, mixed> */
    private function values(InputInterface $input, SymfonyStyle $io): array
    {
        $interactive = $input->isInteractive();
        $accounts = $this->accounts->all();
        if ([] === $accounts) {
            throw new SubscriptionOperationException('Register an account before adding a subscription.', ExitCode::InvalidInput);
        }
        $account = $input->getOption('account');
        $id = $input->getOption('id');
        if ($interactive) {
            $account ??= $io->choice('Account', array_map(static fn ($item): string => $item->id, $accounts));
            $id ??= $io->ask('Unique local subscription ID');
        }
        if (null === $account || null === $id) {
            throw new SubscriptionOperationException('Options --id and --account are required in non-interactive mode.', ExitCode::InvalidInput);
        }
        $name = $input->getOption('name');
        $plan = $input->getOption('plan');
        $type = (string) $input->getOption('billing-type');
        $status = (string) $input->getOption('status');
        $amount = $input->getOption('amount');
        $currency = $input->getOption('currency');
        $interval = $input->getOption('interval');
        $renewsAt = $input->getOption('renews-at');
        $expiresAt = $input->getOption('expires-at');
        if ($interactive) {
            $name ??= $io->ask('Display name (optional)');
            $plan ??= $io->ask('Plan name or identifier (optional)');
            $type = (string) $io->choice('Billing type', array_column(BillingType::cases(), 'value'), $type);
            $status = (string) $io->choice('Status', array_column(SubscriptionStatus::cases(), 'value'), $status);
            if ($io->confirm('Record a billing amount?', false)) {
                $amount ??= $io->ask('Amount (for example 23.00)');
                $currency ??= $io->ask('Currency (for example EUR)');
                $interval ??= $io->choice('Billing interval', array_column(BillingInterval::cases(), 'value'), 'month');
            }
            $renewsAt ??= $io->ask('Renewal date (ISO 8601, optional)');
            $expiresAt ??= $io->ask('Expiration date (ISO 8601, optional)');
        }

        return [
            'id' => trim((string) $id), 'accountId' => trim((string) $account),
            'displayName' => null === $name ? null : (string) $name,
            'planName' => null === $plan ? null : (string) $plan,
            'billingType' => $type, 'status' => $status,
            'billingAmount' => null === $amount ? null : (string) $amount,
            'currency' => null === $currency ? null : (string) $currency,
            'billingInterval' => null === $interval ? null : (string) $interval,
            'renewsAt' => null === $renewsAt ? null : (string) $renewsAt,
            'expiresAt' => null === $expiresAt ? null : (string) $expiresAt,
        ];
    }
}
