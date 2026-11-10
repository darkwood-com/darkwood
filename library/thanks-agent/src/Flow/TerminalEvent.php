<?php

declare(strict_types=1);

namespace App\Flow;

/**
 * Demo terminal lifecycle events (not part of darkwood/flow core).
 * Mutually exclusive success/failure metering for CostLedgerSubscriber.
 */
final class TerminalEvent
{
    /**
     * @Event("App\Flow\Event\CompleteEvent")
     */
    public const COMPLETE = 'app.flow.complete';

    /**
     * @Event("App\Flow\Event\ErrorEvent")
     */
    public const ERROR = 'app.flow.error';
}
