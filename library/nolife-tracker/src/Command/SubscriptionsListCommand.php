<?php

namespace App\Command;

use App\Account\AccountService;
use App\Subscription\Subscription;
use App\Subscription\SubscriptionOperationException;
use App\Subscription\SubscriptionService;
use App\Terminal\ExitCode;
use App\Terminal\JsonOutput;
use App\Terminal\SubscriptionTable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'subscriptions:list', description: 'List local subscriptions and billing arrangements', aliases: ['subscriptions'])]
final class SubscriptionsListCommand extends Command
{
    public function __construct(private readonly SubscriptionService $subscriptions, private readonly AccountService $accounts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Filter by provider')
            ->addOption('account', null, InputOption::VALUE_REQUIRED, 'Filter by account ID')
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
            $subscriptions = $this->subscriptions->all(
                null === $input->getOption('provider') ? null : (string) $input->getOption('provider'),
                null === $input->getOption('account') ? null : (string) $input->getOption('account'),
            );
            $accountMap = [];
            foreach ($this->accounts->all() as $account) {
                $accountMap[$account->id] = $account;
            }
        } catch (SubscriptionOperationException $error) {
            $io->error($error->getMessage());
            return $error->exitCode->value;
        }
        if ('json' === $format) {
            JsonOutput::write($output, ['subscriptions' => array_map(function (Subscription $subscription) use ($accountMap): array {
                return $subscription->toArray() + [
                    'provider' => $accountMap[$subscription->accountId]->providerKey,
                    'products' => array_map(static fn ($entitlement): array => $entitlement->toArray(), $this->subscriptions->entitlements($subscription->id)),
                ];
            }, $subscriptions)]);
            return ExitCode::Success->value;
        }
        $io->title('Nolife Tracker subscriptions');
        if ([] === $subscriptions) {
            $io->note('No subscriptions match the selected filters.');
            return ExitCode::Success->value;
        }
        SubscriptionTable::render($io, $subscriptions, $accountMap);
        $io->note('Billing and product access are local metadata unless explicitly provider-verified.');
        return ExitCode::Success->value;
    }
}
