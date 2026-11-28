<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Customer\Customer;
use App\Entity\User\ShopUser;
use App\Repository\User\ShopUserRepository;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Uid\Uuid;

/** @implements AttributesBasedUserProviderInterface<UserInterface> */
final readonly class DarkwoodShopUserProvider implements AttributesBasedUserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ShopUserRepository $users,
        private DarkwoodOidcSettings $settings,
        #[Autowire(service: 'sylius.shop_user_provider.email_or_name_based')]
        private UserProviderInterface $syliusUsers,
    ) {}

    public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
    {
        if ($attributes === []) {
            return $this->syliusUsers->loadUserByIdentifier($identifier);
        }
        if (!Uuid::isValid($identifier) || ($attributes['sub'] ?? null) !== $identifier || !$this->settings->allowsSubject($identifier)) {
            throw new UserNotFoundException('Darkwood identity is unavailable for this shop.');
        }

        $email = is_string($attributes['email'] ?? null) ? trim($attributes['email']) : '';
        $user = $this->users->findOneBy(['darkwoodUid' => $identifier]);
        if ($user === null) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new UserNotFoundException('Darkwood identity has no usable email address.');
            }

            $canonicalEmail = mb_strtolower($email);
            if ($this->entityManager->getRepository(Customer::class)->findOneBy(['emailCanonical' => $canonicalEmail]) !== null) {
                throw new UserNotFoundException('A Uniflow customer must be linked through their existing Darkwood identity.');
            }

            $customer = new Customer();
            $customer->setEmail($email);
            $customer->setEmailCanonical($canonicalEmail);
            $user = new ShopUser();
            $user->setCustomer($customer);
            $user->setDarkwoodUid($identifier);
            $user->setUsername((string) $user->getUid());
            $user->setUsernameCanonical((string) $user->getUid());
            $user->setEnabled(true);
            $this->entityManager->persist($customer);
            $this->entityManager->persist($user);
        }

        if (!$user instanceof ShopUser || !$user->isEnabled()) {
            throw new UserNotFoundException('The Uniflow user cannot sign in.');
        }
        if (!$user->isVerified() && true === ($attributes['email_verified'] ?? null) && 0 === strcasecmp(trim((string) $user->getEmail()), $email)) {
            $user->setVerifiedAt(new DateTime());
        }

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            throw new UserNotFoundException('The Uniflow account was updated during sign-in. Please try again.');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->syliusUsers->refreshUser($user);
    }

    public function supportsClass(string $class): bool
    {
        return $this->syliusUsers->supportsClass($class);
    }
}
