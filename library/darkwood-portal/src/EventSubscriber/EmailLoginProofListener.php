<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Authenticator\Debug\TraceableAuthenticator;
use Symfony\Component\Security\Http\Authenticator\LoginLinkAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\AuthenticationMethodBadge;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

#[AsEventListener(dispatcher: 'security.event_dispatcher.main')]
final class EmailLoginProofListener
{
    public const string METHOD = 'email';

    public function __invoke(CheckPassportEvent $event): void
    {
        $authenticator = $event->getAuthenticator();
        if ($authenticator instanceof TraceableAuthenticator) {
            $authenticator = $authenticator->getAuthenticator();
        }
        if ($authenticator instanceof LoginLinkAuthenticator) {
            $event->getPassport()->addBadge(new AuthenticationMethodBadge(self::METHOD));
        }
    }
}
