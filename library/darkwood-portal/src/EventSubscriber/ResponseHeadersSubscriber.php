<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE)]
final readonly class ResponseHeadersSubscriber
{
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
    }
}
