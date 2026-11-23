<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class McpAvailabilitySubscriber
{
    public function __construct(private bool $enabled) {}

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 2048)]
    public function onRequest(RequestEvent $event): void
    {
        if (!$this->enabled && '/mcp' === $event->getRequest()->getPathInfo()) {
            $event->setResponse(new JsonResponse(['error' => 'MCP integration disabled.'], 404));
        }
    }
}
