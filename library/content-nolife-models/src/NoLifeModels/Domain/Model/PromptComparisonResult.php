<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Model;

/**
 * Side-by-side outcome for a fixed prompt against N local models (MVP: 2).
 */
final readonly class PromptComparisonResult
{
    /**
     * @param list<ModelInferenceResult> $results
     */
    public function __construct(
        public string $id,
        public string $prompt,
        public ?string $system,
        public \DateTimeImmutable $comparedAt,
        public array $results,
    ) {
        if (\count($this->results) < 2) {
            throw new \InvalidArgumentException('PromptComparisonResult expects at least two model results.');
        }
    }
}
