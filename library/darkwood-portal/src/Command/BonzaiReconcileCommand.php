<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ExternalPurchase;
use App\Enum\PaymentProvider;
use App\Service\Bonzai\BonzaiNotificationHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;

#[AsCommand(
    name: 'app:bonzai:reconcile',
    description: 'Re-read known Bonzai orders and synchronize canonical entitlements',
)]
final class BonzaiReconcileCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BonzaiNotificationHandler $handler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum known orders to verify', '200');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = filter_var($input->getOption('limit'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 1000],
        ]);
        if (false === $limit) {
            $io->error('--limit must be an integer between 1 and 1000.');

            return Command::INVALID;
        }

        $purchases = $this->entityManager->getRepository(ExternalPurchase::class)->findBy(
            ['provider' => PaymentProvider::Bonzai],
            ['verifiedAt' => 'ASC', 'id' => 'ASC'],
            $limit,
        );
        $counts = ['synchronized' => 0, 'pending_association' => 0, 'pending' => 0];
        foreach ($purchases as $purchase) {
            if (!$purchase instanceof ExternalPurchase) {
                continue;
            }
            $result = $this->handler->handle($purchase->getProviderPurchaseId());
            $status = $result['status'];
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        $io->definitionList(
            ['Known Bonzai orders' => count($purchases)],
            ['Entitlements synchronized' => $counts['synchronized']],
            ['Awaiting account association' => $counts['pending_association']],
            ['Provider verification failures' => $counts['pending']],
        );

        if ($counts['pending'] > 0) {
            $io->error('One or more known Bonzai orders could not be verified.');

            return Command::FAILURE;
        }

        $io->success('Known Bonzai orders reconciled.');

        return Command::SUCCESS;
    }
}
