<?php

declare(strict_types=1);

namespace App\Study;

/**
 * Packet that moves through a three-stage Flow: normalize → decide → collect.
 */
final class Payload
{
    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $answers
     * @param array<string, float> $alternatives
     */
    public function __construct(
        public mixed $input,
        public mixed $normalized = null,
        public ?string $decision = null,
        public ?float $confidence = null,
        public array $alternatives = [],
        public array $answers = [],
        public float $latencyMs = 0.0,
        public string $source = 'rule',
        public ?string $error = null,
        public string $job = '',
        public array $context = [],
    ) {
    }

    public static function fromInput(mixed $input): self
    {
        return new self(input: $input, normalized: $input);
    }
}
