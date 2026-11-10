<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Catalog;

use App\NoLifeModels\Domain\Model\ModelModality;

/**
 * Filter criteria for catalog search (PHP-side filtering in MVP).
 */
final readonly class CatalogFilterCriteria
{
    /**
     * @param list<string> $capabilities Any-of match
     */
    public function __construct(
        public ?string $query,
        public ?string $providerId,
        public ?ModelModality $modality,
        public ?int $minContext,
        public ?int $maxContext,
        public array $capabilities,
    ) {
    }

    public static function empty(): self
    {
        return new self(null, null, null, null, null, []);
    }
}
