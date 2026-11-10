<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Model;

/**
 * Normalized timing and token metrics from a single inference call.
 */
final readonly class InferenceMetrics
{
    public function __construct(
        public float $wallClockSeconds,
        public ?float $totalDurationSeconds,
        public ?float $loadDurationSeconds,
        public ?float $promptEvalDurationSeconds,
        public ?float $evalDurationSeconds,
        public ?int $promptEvalCount,
        public ?int $evalCount,
        public ?float $tokensPerSecond,
        public ?string $doneReason,
    ) {
    }
}
