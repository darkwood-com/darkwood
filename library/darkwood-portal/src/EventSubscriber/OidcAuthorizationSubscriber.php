<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\Event\ScopeResolveEvent;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Auto-approves authorization for authenticated first-party OIDC clients.
 *
 * @author Mathieu Ledru
 */
final readonly class OidcAuthorizationSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserRepository $users) {}

    public static function getSubscribedEvents(): array
    {
        return [
            OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE => 'onAuthorizationRequestResolve',
            OAuth2Events::SCOPE_RESOLVE => 'onScopeResolve',
        ];
    }

    public function onAuthorizationRequestResolve(AuthorizationRequestResolveEvent $event): void
    {
        $user = $event->getUser();
        $event->resolveAuthorization($user instanceof User && $user->canAuthenticate());
    }

    public function onScopeResolve(ScopeResolveEvent $event): void
    {
        $subject = $event->getUserIdentifier();
        if (null === $subject) {
            return;
        }

        $user = $this->users->findOneByUuid((string) $subject);
        if (!$user instanceof User || !$user->canAuthenticate()) {
            throw OAuthServerException::invalidGrant('The user is no longer eligible for authorization.');
        }
    }
}
