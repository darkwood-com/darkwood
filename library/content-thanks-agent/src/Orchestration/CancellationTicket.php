<?php

declare(strict_types=1);

namespace App\Orchestration;

/**
 * Demo cooperative cancel ticket — candidate for Flow CancellationToken later.
 */
final class CancellationTicket
{
    private bool $cancelled = false;

    public function cancel(): void
    {
        $this->cancelled = true;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    public function throwIfCancelled(): void
    {
        if ($this->cancelled) {
            throw new \RuntimeException('Flow run cancelled.');
        }
    }
}
