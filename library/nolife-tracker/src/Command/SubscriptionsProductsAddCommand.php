<?php

namespace App\Command;

use App\Provider\ProviderCatalog;
use App\Subscription\EntitlementAccess;
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

#[AsCommand(name: 'subscriptions:products:add', description: 'Associate a provider product with a subscription')]
final class SubscriptionsProductsAddCommand extends Command
{
    public function __construct(private readonly SubscriptionService $subscriptions, private readonly ProviderCatalog $catalog)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Subscription identifier')
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'Product identifier')
            ->addOption('access', null, InputOption::VALUE_REQUIRED, 'unknown or declared', 'declared')
            ->addOption('evidence', null, InputOption::VALUE_REQUIRED, 'Optional user note supporting the declaration')
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
            $provider = $this->catalog->find($account->providerKey);
            if (null === $provider) {
                throw new SubscriptionOperationException('The account provider is no longer in the catalog.', ExitCode::Failure);
            }
            $product = $input->getOption('product');
            $access = (string) $input->getOption('access');
            $evidence = $input->getOption('evidence');
            if ($input->isInteractive()) {
                $product ??= $io->choice('Product', array_map(static fn ($item): string => $item->key, $provider->products));
                $access = (string) $io->choice('Access status', [EntitlementAccess::Declared->value, EntitlementAccess::Unknown->value], $access);
                $evidence ??= $io->ask('Evidence note (optional)');
            }
            if (null === $product) {
                throw new SubscriptionOperationException('The --product option is required in non-interactive mode.', ExitCode::InvalidInput);
            }
            $entitlement = $this->subscriptions->addEntitlement($subscription->id, (string) $product, $access, null === $evidence ? null : (string) $evidence);
        } catch (SubscriptionOperationException $error) {
            $io->error($error->getMessage());
            return $error->exitCode->value;
        }
        if ('json' === $format) {
            JsonOutput::write($output, ['subscriptionId' => $subscription->id, 'product' => $entitlement->toArray()]);
        } else {
            $io->success(sprintf('Product "%s" was associated with subscription "%s" as %s access.', $entitlement->productKey, $subscription->id, $entitlement->accessStatus->value));
            $io->note('Product access does not imply available quota. Manual declarations are never provider-verified.');
        }
        return ExitCode::Success->value;
    }
}
