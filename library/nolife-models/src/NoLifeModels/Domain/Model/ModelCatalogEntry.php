<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Model;

/**
 * Catalog row aligned with models.dev model metadata for listing / comparison tables.
 */
final readonly class ModelCatalogEntry
{
    /**
     * @param list<string> $inputModalities
     * @param list<string> $outputModalities
     * @param list<string> $capabilities
     */
    public function __construct(
        public ModelId $id,
        public ProviderId $providerId,
        public string $name,
        public ?int $contextLength,
        public array $inputModalities,
        public array $outputModalities,
        public array $capabilities,
        public ?string $family = null,
        public ?float $costPerMillionInput = null,
        public ?float $costPerMillionOutput = null,
        public ?float $costPerMillionReasoning = null,
        public ?float $costPerMillionOver200kInput = null,
        public ?float $costPerMillionOver200kOutput = null,
        public ?int $limitMaxInput = null,
        public ?int $limitMaxOutput = null,
        public bool $reasoningEnabled = false,
        public bool $toolCallEnabled = false,
        public ?bool $structuredOutput = null,
        public bool $openWeights = false,
        public ?string $knowledge = null,
        public ?string $releaseDate = null,
    ) {
    }
}
