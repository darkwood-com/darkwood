<?php

declare(strict_types=1);

namespace App\NoLifeModels\Application\Catalog;

use App\NoLifeModels\Domain\Catalog\CatalogFilterCriteria;
use App\NoLifeModels\Domain\Catalog\ModelCatalogRepositoryInterface;
use App\NoLifeModels\Domain\Model\ModelCatalogEntry;

final class SearchCatalogModelsHandler
{
    public function __construct(
        private readonly ModelCatalogRepositoryInterface $catalog,
    ) {
    }

    /**
     * @return list<ModelCatalogEntry>
     */
    public function handle(CatalogFilterCriteria $criteria): array
    {
        $all = $this->catalog->findAll();

        return array_values(array_filter($all, static function (ModelCatalogEntry $entry) use ($criteria): bool {
            if ($criteria->providerId !== null && $entry->providerId->value !== $criteria->providerId) {
                return false;
            }
            if ($criteria->minContext !== null && ($entry->contextLength === null || $entry->contextLength < $criteria->minContext)) {
                return false;
            }
            if ($criteria->maxContext !== null && ($entry->contextLength === null || $entry->contextLength > $criteria->maxContext)) {
                return false;
            }
            if ($criteria->modality !== null) {
                $needle = $criteria->modality->value;
                $modalities = [...$entry->inputModalities, ...$entry->outputModalities];
                if (!\in_array($needle, $modalities, true)) {
                    return false;
                }
            }
            foreach ($criteria->capabilities as $cap) {
                if (!\in_array($cap, $entry->capabilities, true)) {
                    return false;
                }
            }
            if ($criteria->query !== null && $criteria->query !== '') {
                $q = mb_strtolower($criteria->query);
                $hay = mb_strtolower($entry->name.' '.$entry->id->value.' '.$entry->providerId->value);
                if (!str_contains($hay, $q)) {
                    return false;
                }
            }

            return true;
        }));
    }
}
