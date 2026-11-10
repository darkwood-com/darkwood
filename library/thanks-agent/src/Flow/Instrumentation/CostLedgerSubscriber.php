<?php

declare(strict_types=1);

namespace App\Flow\Instrumentation;

use App\Flow\Event\CompleteEvent;
use App\Flow\Event\ErrorEvent;
use App\Flow\TerminalEvent;
use Flow\Event;
use Flow\Event\AsyncEvent;
use Flow\Event\PopEvent;
use Flow\Event\PullEvent;
use Flow\Event\PushEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to Flow lifecycle + demo terminal events and feeds a CostLedger.
 */
final class CostLedgerSubscriber implements EventSubscriberInterface
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
            TerminalEvent::COMPLETE => ['onComplete', 0],
            TerminalEvent::ERROR => ['onError', 0],
        ];
    }

    public function onPush(PushEvent $event): void
    {
        $this->ledger->recordPush();
    }

    public function onPull(PullEvent $event): void
    {
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

    public function onComplete(CompleteEvent $event): void
    {
        $metrics = $event->getMetrics();
        $this->ledger->recordComplete(
            (string) ($metrics['label'] ?? 'job'),
            (int) ($metrics['tokens'] ?? 0),
            (float) ($metrics['cost'] ?? 0.0),
        );
    }

    public function onError(ErrorEvent $event): void
    {
        $metrics = $event->getMetrics();
        $this->ledger->recordError(
            (string) ($metrics['label'] ?? 'job'),
            (int) ($metrics['tokens'] ?? 0),
            (float) ($metrics['cost'] ?? 0.0),
        );
    }
}
