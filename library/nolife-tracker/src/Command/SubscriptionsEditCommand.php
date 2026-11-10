<?php

namespace App\Command;

use App\Subscription\BillingInterval;
use App\Subscription\BillingType;
use App\Subscription\SubscriptionOperationException;
use App\Subscription\SubscriptionService;
use App\Subscription\SubscriptionStatus;
use App\Terminal\ExitCode;
use App\Terminal\JsonOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'subscriptions:edit', description: 'Edit subscription and billing metadata')]
final class SubscriptionsEditCommand extends Command
{
    public function __construct(private readonly SubscriptionService $subscriptions)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Subscription identifier')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Display name')
            ->addOption('clear-name', null, InputOption::VALUE_NONE, 'Remove display name')
            ->addOption('plan', null, InputOption::VALUE_REQUIRED, 'Plan name or identifier')
            ->addOption('clear-plan', null, InputOption::VALUE_NONE, 'Remove plan name')
            ->addOption('billing-type', null, InputOption::VALUE_REQUIRED, 'Billing type')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Subscription status')
            ->addOption('amount', null, InputOption::VALUE_REQUIRED, 'Billing amount')
            ->addOption('currency', null, InputOption::VALUE_REQUIRED, 'Currency code')
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Billing interval')
            ->addOption('clear-billing', null, InputOption::VALUE_NONE, 'Remove amount, currency, and interval')
            ->addOption('renews-at', null, InputOption::VALUE_REQUIRED, 'ISO 8601 renewal date')
            ->addOption('clear-renews-at', null, InputOption::VALUE_NONE, 'Remove renewal date')
            ->addOption('expires-at', null, InputOption::VALUE_REQUIRED, 'ISO 8601 expiration date')
            ->addOption('clear-expires-at', null, InputOption::VALUE_NONE, 'Remove expiration date')
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
            $current = $this->subscriptions->get((string) $input->getArgument('id'));
            if (!$input->isInteractive() && !$this->hasChanges($input)) {
                throw new SubscriptionOperationException('At least one edit option is required in non-interactive mode.', ExitCode::InvalidInput);
            }
            $name = $input->getOption('clear-name') ? null : ($input->getOption('name') ?? $current->displayName);
            $plan = $input->getOption('clear-plan') ? null : ($input->getOption('plan') ?? $current->planName);
            $type = (string) ($input->getOption('billing-type') ?? $current->billingType->value);
            $status = (string) ($input->getOption('status') ?? $current->status->value);
            $amount = $input->getOption('clear-billing') ? null : ($input->getOption('amount') ?? (null === $current->billingAmountMinor ? null : number_format($current->billingAmountMinor / 100, 2, '.', '')));
            $currency = $input->getOption('clear-billing') ? null : ($input->getOption('currency') ?? $current->currency);
            $interval = $input->getOption('clear-billing') ? null : ($input->getOption('interval') ?? $current->billingInterval?->value);
            $renews = $input->getOption('clear-renews-at') ? null : ($input->getOption('renews-at') ?? $current->renewsAt?->format(DATE_ATOM));
            $expires = $input->getOption('clear-expires-at') ? null : ($input->getOption('expires-at') ?? $current->expiresAt?->format(DATE_ATOM));
            if ($input->isInteractive()) {
                $name = $io->ask('Display name (empty removes it)', $name ?? '');
                $plan = $io->ask('Plan name (empty removes it)', $plan ?? '');
                $type = (string) $io->choice('Billing type', array_column(BillingType::cases(), 'value'), $type);
                $status = (string) $io->choice('Status', array_column(SubscriptionStatus::cases(), 'value'), $status);
                if ($io->confirm('Keep or record an exact billing amount?', null !== $amount)) {
                    $amount = $io->ask('Amount', $amount);
                    $currency = $io->ask('Currency', $currency);
                    $interval = $io->choice('Interval', array_column(BillingInterval::cases(), 'value'), $interval ?? 'month');
                } else {
                    $amount = $currency = $interval = null;
                }
                $renews = $io->ask('Renewal date (empty removes it)', $renews ?? '');
                $expires = $io->ask('Expiration date (empty removes it)', $expires ?? '');
            }
            $subscription = $this->subscriptions->edit(
                $current->id, null === $name ? null : (string) $name, null === $plan ? null : (string) $plan,
                $type, $status, null === $amount ? null : (string) $amount,
                null === $currency ? null : (string) $currency, null === $interval ? null : (string) $interval,
                null === $renews ? null : (string) $renews, null === $expires ? null : (string) $expires,
            );
        } catch (SubscriptionOperationException $error) {
            $io->error($error->getMessage());
            return $error->exitCode->value;
        }
        if ('json' === $format) {
            JsonOutput::write($output, ['subscription' => $subscription->toArray()]);
        } else {
            $io->success(sprintf('Subscription "%s" was updated.', $subscription->id));
        }
        return ExitCode::Success->value;
    }

    private function hasChanges(InputInterface $input): bool
    {
        foreach (['name', 'plan', 'billing-type', 'status', 'amount', 'currency', 'interval', 'renews-at', 'expires-at'] as $option) {
            if (null !== $input->getOption($option)) {
                return true;
            }
        }
        foreach (['clear-name', 'clear-plan', 'clear-billing', 'clear-renews-at', 'clear-expires-at'] as $option) {
            if ($input->getOption($option)) {
                return true;
            }
        }
        return false;
    }
}
