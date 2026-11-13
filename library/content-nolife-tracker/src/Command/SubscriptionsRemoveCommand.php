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

#[AsCommand(name: 'subscriptions:remove', description: 'Remove a local subscription without removing its account')]
final class SubscriptionsRemoveCommand extends Command
{
    public function __construct(private readonly SubscriptionService $subscriptions)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Subscription identifier')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Remove without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = (string) $input->getArgument('id');
        try {
            $subscription = $this->subscriptions->get($id);
            if (!$input->getOption('force')) {
                if (!$input->isInteractive()) {
                    throw new SubscriptionOperationException('The --force option is required in non-interactive mode.', ExitCode::InvalidInput);
                }
                if (!$io->confirm(sprintf('Remove subscription "%s" and its product entitlements?', $id), false)) {
                    $io->note('No subscription was removed.');
                    return ExitCode::Success->value;
                }
            }
            $this->subscriptions->remove($id);
        } catch (SubscriptionOperationException $error) {
            $io->error($error->getMessage());
            return $error->exitCode->value;
        }
        $io->success(sprintf('Subscription "%s" was removed; account "%s" remains registered.', $subscription->id, $subscription->accountId));
        return ExitCode::Success->value;
    }
}
