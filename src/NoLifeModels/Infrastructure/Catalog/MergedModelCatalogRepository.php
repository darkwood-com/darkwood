<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Catalog;

use App\NoLifeModels\Domain\Catalog\ModelCatalogRepositoryInterface;
use App\NoLifeModels\Domain\Model\ModelCatalogEntry;

/**
 * Merges JSON-backed catalog with in-memory defaults; JSON wins on duplicate ids.
 */
final class MergedModelCatalogRepository implements ModelCatalogRepositoryInterface
{
    public function __construct(
        private readonly JsonModelCatalogRepository $json,
        private readonly InMemoryModelCatalogRepository $memory,
    ) {
    }

    public function findAll(): array
    {
        $byId = [];
        foreach ($this->memory->findAll() as $entry) {
            $byId[$entry->id->value] = $entry;
        }
        foreach ($this->json->findAll() as $entry) {
            $byId[$entry->id->value] = $entry;
        }

        return array_values($byId);
    }
}
