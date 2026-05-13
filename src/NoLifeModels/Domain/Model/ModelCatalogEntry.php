<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Model;

/**
 * Reduced catalog row inspired by models.dev metadata (not a full mirror).
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
    ) {
    }
}
