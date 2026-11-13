<?php

declare(strict_types=1);

namespace App\Flow\Instrumentation;

/**
 * Declares production value for a job/label. Noise jobs score 0.
 */
final readonly class ValueTag
{
    public function __construct(
        public string $label,
        public int $value,
    ) {}
}
