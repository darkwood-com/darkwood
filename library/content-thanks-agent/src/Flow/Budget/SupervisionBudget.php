<?php

declare(strict_types=1);

namespace App\Flow\Budget;

use function max;
use function min;

/**
 * Human/supervision WIP gate for the thanks-agent demo.
 * Prefer native clamp() on PHP 8.6+ when available.
 */
final class SupervisionBudget
{
    private int $inFlight = 0;

    public function __construct(
        private readonly int $max = 5,
    ) {
        if ($this->max < 1) {
            throw new \InvalidArgumentException('Supervision budget must be >= 1.');
        }
    }

    public function max(): int
    {
        return $this->max;
    }

    public function inFlight(): int
    {
        return $this->inFlight;
    }

    public function remaining(): int
    {
        return max(0, $this->max - $this->inFlight);
    }

    public function tryAcquire(): bool
    {
        if ($this->inFlight >= $this->max) {
            return false;
        }
        ++$this->inFlight;

        return true;
    }

    public function release(): void
    {
        $this->inFlight = max(0, $this->inFlight - 1);
    }

    public function clampConcurrency(int $requested): int
    {
        if (function_exists('clamp')) {
            return (int) clamp($requested, 1, $this->max);
        }

        return max(1, min($requested, $this->max));
    }
}
