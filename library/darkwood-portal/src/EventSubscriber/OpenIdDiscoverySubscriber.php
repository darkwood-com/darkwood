<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(event: KernelEvents::RESPONSE)]
final readonly class OpenIdDiscoverySubscriber
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if ('openid_connect_discovery' !== $event->getRequest()->attributes->get('_route')) {
            return;
        }

        $response = $event->getResponse();
        if (!$response instanceof JsonResponse || !$response->isSuccessful()) {
            return;
        }

        $metadata = json_decode($response->getContent() ?: '', true, flags: JSON_THROW_ON_ERROR);
        $metadata += [
            'userinfo_endpoint' => $this->urlGenerator->generate('openid_connect_userinfo', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'scopes_supported' => ['openid', 'profile', 'email', 'roles', 'entitlements'],
            'code_challenge_methods_supported' => ['S256'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post', 'none'],
        ];

        $response->setData($metadata);
    }
}
