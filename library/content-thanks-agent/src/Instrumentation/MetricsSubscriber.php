<?php

declare(strict_types=1);

namespace App\Instrumentation;

use Flow\Event;
use Flow\Event\AsyncEvent;
use Flow\Event\PopEvent;
use Flow\Event\PullEvent;
use Flow\Event\PushEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Hooks Flow PUSH/PULL/POP/ASYNC — does not invent a parallel bus.
 */
final class MetricsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CostLedger $ledger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Event::PUSH => ['onPush', -100],
            Event::PULL => ['onPull', -100],
            Event::POP => ['onPop', -100],
            Event::ASYNC => ['onAsync', -100],
        ];
    }

    public function onPush(PushEvent $event): void
    {
        $this->ledger->recordPush();
    }

    public function onPull(PullEvent $event): void
    {
        // FiberDriver polls PULL in a tight loop; only meter productive pulls.
        if ([] !== $event->getIps()) {
            $this->ledger->recordPull();
        }
    }

    public function onPop(PopEvent $event): void
    {
        $this->ledger->recordPop();
    }

    public function onAsync(AsyncEvent $event): void
    {
        $this->ledger->recordAsync();
    }
}
