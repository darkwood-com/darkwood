<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User\ShopUser;
use App\Entity\User\UserOAuth;
use App\Repository\User\ShopUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

#[AsCommand(name: 'app:user:link-oidc', description: 'Explicitly link a ShopUser UID to an OIDC subject')]
final class LinkOidcUserCommand extends Command
{
    public function __construct(
        private readonly ShopUserRepository $shopUsers,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('shop-user-uid', InputArgument::REQUIRED, 'Local Uniflow ShopUser UID')
            ->addArgument('provider', InputArgument::REQUIRED, 'Stable local name for the OIDC provider')
            ->addArgument('subject', InputArgument::REQUIRED, 'Stable OIDC sub claim')
            ->addOption('replace', null, InputOption::VALUE_NONE, 'Replace this user\'s existing association for the provider')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $uid = (string) $input->getArgument('shop-user-uid');
        $provider = trim((string) $input->getArgument('provider'));
        $subject = trim((string) $input->getArgument('subject'));
        $user = $this->shopUsers->findOneBy(['uid' => $uid]);

        if (!$user instanceof ShopUser) {
            $io->error('No ShopUser matches the supplied local UID.');

            return Command::FAILURE;
        }

        if ('' === $provider || '' === $subject) {
            $io->error('The provider and subject cannot be empty.');

            return Command::INVALID;
        }

        $repository = $this->entityManager->getRepository(UserOAuth::class);
        $collision = $repository->findOneBy(['provider' => $provider, 'identifier' => $subject]);
        if ($collision instanceof UserOAuth && $collision->getUser() !== $user) {
            $io->error('This provider subject is already linked to another ShopUser.');

            return Command::FAILURE;
        }

        $account = $user->getOAuthAccount($provider);
        if (null !== $account && $account->getIdentifier() !== $subject && !$input->getOption('replace')) {
            $io->error('This ShopUser already has an association for the provider; pass --replace to change it explicitly.');

            return Command::FAILURE;
        }

        if (null === $account) {
            $account = new UserOAuth();
            $account->setProvider($provider);
            $user->addOAuthAccount($account);
            $this->entityManager->persist($account);
        }

        $account->setIdentifier($subject);
        $this->entityManager->flush();
        $io->success(sprintf('OIDC subject for provider "%s" linked to ShopUser %s.', $provider, $uid));

        return Command::SUCCESS;
    }
}
