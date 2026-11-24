<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE)]
final readonly class ResponseHeadersSubscriber
{
    private const string CROSS_ORIGIN_ISOLATED_EMBED_MARKER = 'data-cross-origin-isolated';

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');

        $attributes = $event->getRequest()->attributes;
        if ('security_login' === $attributes->get('_canonical_route', $attributes->get('_route'))) {
            $headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        if ('blog_article' !== $attributes->get('_route')) {
            return;
        }

        $content = $event->getResponse()->getContent();
        if (false === $content || !str_contains($content, self::CROSS_ORIGIN_ISOLATED_EMBED_MARKER)) {
            return;
        }

        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->set('Cross-Origin-Embedder-Policy', 'credentialless');
        $headers->set('Permissions-Policy', 'cross-origin-isolated=(self "https://apps.darkwood.com")');
    }
}
