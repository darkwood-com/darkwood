<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class FlowEmbedHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['allowDarkwoodEmbed', -100]];
    }

    public function allowDarkwoodEmbed(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || 'app_shop_flow_embed' !== $event->getRequest()->attributes->get('_route')) {
            return;
        }

        $event->getResponse()->headers->remove('X-Frame-Options');
    }
}
