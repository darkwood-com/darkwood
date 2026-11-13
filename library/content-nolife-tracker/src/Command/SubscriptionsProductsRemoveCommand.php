<?php

namespace App\Command;

use App\Subscription\SubscriptionOperationException;
use App\Subscription\SubscriptionService;
use App\Terminal\ExitCode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'subscriptions:products:remove', description: 'Remove a product entitlement from a subscription')]
final class SubscriptionsProductsRemoveCommand extends Command
{
    public function __construct(private readonly SubscriptionService $subscriptions)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Subscription identifier')
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'Product identifier')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Remove without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $id = (string) $input->getArgument('id');
            $entitlements = $this->subscriptions->entitlements($id);
            $product = $input->getOption('product');
            if ($input->isInteractive() && null === $product) {
                if ([] === $entitlements) {
                    throw new SubscriptionOperationException('This subscription has no product entitlements.', ExitCode::NotFound);
                }
                $product = $io->choice('Product', array_map(static fn ($item): string => $item->productKey, $entitlements));
            }
            if (null === $product) {
                throw new SubscriptionOperationException('The --product option is required in non-interactive mode.', ExitCode::InvalidInput);
            }
            if (!$input->getOption('force')) {
                if (!$input->isInteractive()) {
                    throw new SubscriptionOperationException('The --force option is required in non-interactive mode.', ExitCode::InvalidInput);
                }
                if (!$io->confirm(sprintf('Remove product "%s" from subscription "%s"?', $product, $id), false)) {
                    $io->note('No entitlement was removed.');
                    return ExitCode::Success->value;
                }
            }
            $this->subscriptions->removeEntitlement($id, (string) $product);
        } catch (SubscriptionOperationException $error) {
            $io->error($error->getMessage());
            return $error->exitCode->value;
        }
        $io->success(sprintf('Product "%s" was removed from subscription "%s".', $product, $id));
        return ExitCode::Success->value;
    }
}
