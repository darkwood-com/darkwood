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

#[AsCommand(name: 'subscriptions:products', description: 'List product entitlements for a subscription')]
final class SubscriptionsProductsCommand extends Command
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
            $id = (string) $input->getArgument('id');
            $entitlements = $this->subscriptions->entitlements($id);
        } catch (SubscriptionOperationException $error) {
            $io->error($error->getMessage());
            return $error->exitCode->value;
        }
        if ('json' === $format) {
            JsonOutput::write($output, ['subscriptionId' => $id, 'products' => array_map(static fn (Entitlement $item): array => $item->toArray(), $entitlements)]);
        } elseif ([] === $entitlements) {
            $io->note('No product entitlements are associated with this subscription.');
        } else {
            $io->table(['Product', 'Access', 'Source', 'Last verified'], array_map(static fn (Entitlement $item): array => [
                $item->productKey, $item->accessStatus->value, $item->verificationSource ?? '—', $item->verifiedAt?->format(DATE_ATOM) ?? 'never',
            ], $entitlements));
        }
        return ExitCode::Success->value;
    }
}
