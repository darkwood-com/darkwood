<?php

namespace App\Command;

use App\Subscription\Entitlement;
use App\Subscription\SubscriptionOperationException;
use App\Subscription\SubscriptionService;
use App\Terminal\ExitCode;
use App\Terminal\JsonOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'subscriptions:show', description: 'Inspect a subscription and its product entitlements')]
final class SubscriptionsShowCommand extends Command
{
    public function __construct(private readonly SubscriptionService $subscriptions)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Subscription identifier')
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
            $subscription = $this->subscriptions->get((string) $input->getArgument('id'));
            $account = $this->subscriptions->accountFor($subscription);
            $entitlements = $this->subscriptions->entitlements($subscription->id);
        } catch (SubscriptionOperationException $error) {
            $io->error($error->getMessage());
            return $error->exitCode->value;
        }
        if ('json' === $format) {
            JsonOutput::write($output, ['subscription' => $subscription->toArray() + [
                'provider' => $account->providerKey,
                'products' => array_map(static fn (Entitlement $entitlement): array => $entitlement->toArray(), $entitlements),
            ]]);
            return ExitCode::Success->value;
        }
        $io->title($subscription->displayName ?? $subscription->id);
        $billing = null === $subscription->billingAmountMinor
            ? $subscription->billingType->value
            : sprintf('%.2f %s%s (user declared)', $subscription->billingAmountMinor / 100, $subscription->currency, null === $subscription->billingInterval ? '' : '/'.$subscription->billingInterval->value);
        $io->definitionList(
            ['Provider / account' => $account->providerKey.' / '.$account->id],
            ['Plan' => $subscription->planName ?? 'unknown'],
            ['Billing' => $billing],
            ['Status' => $subscription->status->value],
            ['Renews' => $subscription->renewsAt?->format(DATE_ATOM) ?? 'unknown'],
            ['Expires' => $subscription->expiresAt?->format(DATE_ATOM) ?? 'unknown'],
        );
        $io->section('Products');
        if ([] === $entitlements) {
            $io->text('None declared.');
        } else {
            $io->table(['Product', 'Access', 'Source', 'Evidence'], array_map(static fn (Entitlement $item): array => [
                $item->productKey, $item->accessStatus->value, $item->verificationSource ?? '—', $item->verificationEvidence ?? '—',
            ], $entitlements));
        }
        $io->note('An entitlement records access only; it never implies available quota.');
        return ExitCode::Success->value;
    }
}
