<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User\ShopUser;
use App\Entity\User\UserOAuth;
use App\Repository\User\ShopUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function sprintf;

/**
 * Resolves an already-linked OIDC subject to its local Sylius ShopUser.
 *
 * @implements AttributesBasedUserProviderInterface<ShopUser>
 */
final readonly class OidcShopUserProvider implements AttributesBasedUserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ShopUserRepository $shopUsers,
        private string $oidcProvider,
    ) {}

    /**
     * @param array<string, mixed> $attributes
     */
    public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
    {
        $account = $this->entityManager->getRepository(UserOAuth::class)->findOneBy([
            'provider' => $this->oidcProvider,
            'identifier' => $identifier,
        ]);
        $user = $account?->getUser();

        if (!$user instanceof ShopUser) {
            $exception = new UserNotFoundException(sprintf('No ShopUser is linked to OIDC subject "%s".', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof ShopUser || '' === $user->getUid()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $refreshedUser = $this->shopUsers->findOneBy(['uid' => $user->getUid()]);
        if (!$refreshedUser instanceof ShopUser) {
            $exception = new UserNotFoundException('The linked ShopUser no longer exists.');
            $exception->setUserIdentifier($user->getUid());

            throw $exception;
        }

        return $refreshedUser;
    }

    public function supportsClass(string $class): bool
    {
        return ShopUser::class === $class || is_subclass_of($class, ShopUser::class);
    }
}
