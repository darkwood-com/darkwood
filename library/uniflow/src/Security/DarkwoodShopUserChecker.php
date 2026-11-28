<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User\ShopUser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class DarkwoodShopUserChecker implements UserCheckerInterface
{
    public function __construct(
        #[Autowire(service: 'security.user_checker.chain.shop')]
        private UserCheckerInterface $syliusChecker,
    ) {}

    public function checkPreAuth(UserInterface $user): void
    {
        $this->syliusChecker->checkPreAuth($user);
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if ($user instanceof ShopUser && $token !== null && $token->hasAttribute('oidc_id_token')) {
            // Darkwood Identity decides which of its legacy users are eligible to sign in.
            // A local suspension is still enforced, and an unknown email stays unverified.
            if (!$user->isEnabled()) {
                throw new DisabledException('The Uniflow account is disabled.');
            }

            return;
        }

        $this->syliusChecker->checkPostAuth($user, $token);
    }
}
