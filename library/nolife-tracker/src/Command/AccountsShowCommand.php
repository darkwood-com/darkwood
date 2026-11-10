<?php

namespace App\Command;

use App\Account\AccountOperationException;
use App\Account\AccountService;
use App\Subscription\Entitlement;
use App\Subscription\Subscription;
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

#[AsCommand(name: 'accounts:show', description: 'Inspect a locally registered account')]
final class AccountsShowCommand extends Command
{
    public function __construct(
        private readonly AccountService $accounts,
        private readonly SubscriptionService $subscriptions,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Local account identifier')
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

        try {
            $account = $this->accounts->get((string) $input->getArgument('id'));
        } catch (AccountOperationException $error) {
            $io->error($error->getMessage());

            return $error->exitCode->value;
        }
        $isDefault = $this->accounts->isDefault($account);
        $subscriptions = $this->subscriptions->all(accountId: $account->id);
        $subscriptionData = array_map(fn (Subscription $subscription): array => $subscription->toArray() + [
            'products' => array_map(static fn (Entitlement $entitlement): array => $entitlement->toArray(), $this->subscriptions->entitlements($subscription->id)),
        ], $subscriptions);
        $legacy = $this->accounts->unresolvedLegacyProduct($account->id);
        $data = $account->toSafeArray(true) + [
            'providerDefault' => $isDefault,
            'subscriptions' => $subscriptionData,
            'unresolvedLegacyProduct' => $legacy,
        ];

        if ('json' === $format) {
            JsonOutput::write($output, ['account' => $data]);

            return ExitCode::Success->value;
        }

        $io->title(sprintf('%s%s', $account->id, null === $account->alias ? '' : ' — '.$account->alias));
        $io->definitionList(
            ['Provider' => $account->providerKey],
            ['Local registration' => 'registered'],
            ['Monitoring' => $account->monitoringEnabled ? 'enabled' : 'disabled'],
            ['Authentication method' => $account->authenticationMethod->label()],
            ['Authentication configured' => $account->authenticationConfigured() ? 'yes' : 'no'],
            ['Authentication reference' => $account->redactedAuthenticationReference() ?? 'none'],
            ['Authentication verified' => $account->authenticationVerificationState->label()],
            ['Provider default' => $isDefault ? 'yes' : 'no'],
            ['Created' => $account->createdAt->format(DATE_ATOM)],
            ['Updated' => $account->updatedAt->format(DATE_ATOM)],
        );

        $io->section('Subscriptions');
        if ([] === $subscriptions) {
            $io->text('None');
        }
        foreach ($subscriptions as $subscription) {
            $entitlements = $this->subscriptions->entitlements($subscription->id);
            $io->writeln(sprintf('<info>%s</info> — %s', $subscription->displayName ?? $subscription->id, $subscription->billingType->value));
            $io->listing(array_map(
                static fn (Entitlement $entitlement): string => sprintf('%s (%s)', $entitlement->productKey, $entitlement->accessStatus->value),
                $entitlements,
            ));
        }
        if (null !== $legacy) {
            $io->warning(sprintf(
                'Migrated v0.1 product link "%s" remains unresolved%s; create a subscription and entitlement explicitly.',
                $legacy['product_key'],
                $legacy['was_product_default'] ? ' (formerly the product default)' : '',
            ));
        }

        return ExitCode::Success->value;
    }
}
