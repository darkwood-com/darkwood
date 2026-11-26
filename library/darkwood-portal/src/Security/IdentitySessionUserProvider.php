<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/** @implements AttributesBasedUserProviderInterface<User> */
#[AsDecorator(decorates: 'security.user.provider.concrete.app_user_provider')]
final readonly class IdentitySessionUserProvider implements AttributesBasedUserProviderInterface, PasswordUpgraderInterface
{
    /** @param EntityUserProvider<User> $inner */
    public function __construct(#[AutowireDecorated] private EntityUserProvider $inner) {}

    public function loadUserByIdentifier(string $identifier, ?array $attributes = null): UserInterface
    {
        return $this->inner->loadUserByIdentifier($identifier, $attributes);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        $refreshed = $this->inner->refreshUser($user);
        if (!$refreshed->canAuthenticate()) {
            throw new UserNotFoundException();
        }

        return $refreshed;
    }

    public function supportsClass(string $class): bool
    {
        return $this->inner->supportsClass($class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $this->inner->upgradePassword($user, $newHashedPassword);
    }
}
