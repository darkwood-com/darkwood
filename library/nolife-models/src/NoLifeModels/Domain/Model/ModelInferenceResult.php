<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Model;

final readonly class ModelInferenceResult
{
    /**
     * @param array<string, mixed>|null $rawResponse Optional payload fragment for exports / debugging.
     */
    public function __construct(
        public string $modelName,
        public string $responseText,
        public ?string $error,
        public InferenceMetrics $metrics,
        public ?array $rawResponse = null,
    ) {
    }

    public function hasError(): bool
    {
        return null !== $this->error && $this->error !== '';
    }
}
