<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Catalog;

use App\NoLifeModels\Domain\Catalog\ModelCatalogRepositoryInterface;
use App\NoLifeModels\Domain\Model\ModelCatalogEntry;

/**
 * Merges models.dev API with JSON overrides and in-memory fallbacks for unknown ids.
 */
final class MergedModelCatalogRepository implements ModelCatalogRepositoryInterface
{
    public function __construct(
        private readonly ModelsDevApiCatalogRepository $modelsDev,
        private readonly JsonModelCatalogRepository $json,
        private readonly InMemoryModelCatalogRepository $memory,
    ) {
    }

    public function findAll(): array
    {
        $byId = [];
        foreach ($this->modelsDev->findAll() as $entry) {
            $byId[$entry->id->value] = $entry;
        }
        foreach ($this->json->findAll() as $entry) {
            $byId[$entry->id->value] = $entry;
        }
        foreach ($this->memory->findAll() as $entry) {
            if (!isset($byId[$entry->id->value])) {
                $byId[$entry->id->value] = $entry;
            }
        }

        return array_values($byId);
    }
}
