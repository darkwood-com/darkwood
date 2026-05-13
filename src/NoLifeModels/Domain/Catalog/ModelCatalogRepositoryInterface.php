<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Catalog;

use App\NoLifeModels\Domain\Model\ModelCatalogEntry;

interface ModelCatalogRepositoryInterface
{
    /**
     * @return list<ModelCatalogEntry>
     */
    public function findAll(): array;
}
