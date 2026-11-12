<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\Bonzai\BonzaiNotificationHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:bonzai:associate',
    description: 'Associate a server-verified Bonzai order after explicit account-ownership confirmation.',
)]
final class BonzaiAssociateCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly BonzaiNotificationHandler $handler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('order-id', InputArgument::REQUIRED, 'Bonzai order identifier')
            ->addArgument('darkwood-uid', InputArgument::REQUIRED, 'Immutable Darkwood UUID')
            ->addOption('ownership-confirmed', null, InputOption::VALUE_NONE, 'Confirm that account ownership was verified outside the unsigned webhook')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (!$input->getOption('ownership-confirmed')) {
            $io->error('Refusing association without explicit ownership confirmation.');

            return Command::INVALID;
        }

        $orderId = (string) $input->getArgument('order-id');
        $uid = (string) $input->getArgument('darkwood-uid');
        $user = $this->users->findOneByUuid($uid);
        if (null === $user) {
            $io->error('Darkwood user not found.');

            return Command::FAILURE;
        }

        $purchase = $this->handler->associateVerifiedOrder($orderId, $user);
        $io->success(sprintf('Verified order %s associated; canonical state: %s.', $orderId, $purchase->getState()->value));

        return Command::SUCCESS;
    }
}
