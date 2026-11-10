<?php

declare(strict_types=1);

namespace App\NoLifeModels\Application\Comparison;

final readonly class RunPromptComparisonInput
{
    public function __construct(
        public string $modelA,
        public string $modelB,
        public string $prompt,
        public ?string $system,
        public float $temperature = 0.7,
        public float $topP = 0.9,
    ) {
    }
}
