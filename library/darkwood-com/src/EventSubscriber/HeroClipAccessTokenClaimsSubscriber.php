<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use League\Bundle\OAuth2ServerBundle\Event\AccessTokenExtraClaimsResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/** Binds tokens from the dedicated HeroClip client to the HeroClip MCP resource. */
final readonly class HeroClipAccessTokenClaimsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requests,
        #[Autowire('%env(default:heroclip_oauth_resource_default:HEROCLIP_OAUTH_RESOURCE)%')]
        private string $heroClipResource,
        #[Autowire('%env(default:heroclip_oauth_client_default:HEROCLIP_OAUTH_CLIENT_ID)%')]
        private string $heroClipClientId,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [OAuth2Events::ACCESS_TOKEN_EXTRA_CLAIMS_RESOLVE => 'onClaimsResolve'];
    }

    public function onClaimsResolve(AccessTokenExtraClaimsResolveEvent $event): void
    {
        $request = $this->requests->getCurrentRequest();
        $resource = $request?->request->getString('resource') ?? '';
        if (null === $request || '' === $this->heroClipResource || '' === $this->heroClipClientId
            || !hash_equals($this->heroClipResource, $resource)
            || !hash_equals($this->heroClipClientId, $event->getClient()->getIdentifier())) {
            return;
        }

        $event->setExtraClaims([
            'iss' => $request->getSchemeAndHttpHost(),
            'resource' => $resource,
        ]);
    }
}
