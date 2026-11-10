<?php

declare(strict_types=1);

namespace App\Token;

/**
 * Size snapshot for a context blob.
 *
 * estimatedTokens is an approximation (bytes / 4), not a billed provider count.
 */
final class ContextMetrics
{
    public function __construct(
        public readonly int $bytes,
        public readonly int $characters,
        public readonly int $lines,
        public readonly int $estimatedTokens,
    ) {}

    public function reductionPercent(self $after): float
    {
        if ($this->estimatedTokens === 0) {
            return 0.0;
        }

        return round((1 - ($after->estimatedTokens / $this->estimatedTokens)) * 100, 1);
    }
}
