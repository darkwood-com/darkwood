<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\LiveComponent;

use App\NoLifeModels\Application\Catalog\SearchCatalogModelsHandler;
use App\NoLifeModels\Domain\Catalog\CatalogFilterCriteria;
use App\NoLifeModels\Domain\Model\ModelCatalogEntry;
use App\NoLifeModels\Domain\Model\ModelModality;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'CatalogExplorer')]
final class CatalogExplorerComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public function __construct(
        private readonly SearchCatalogModelsHandler $searchCatalogModels,
    ) {
    }

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public string $providerId = '';

    #[LiveProp(writable: true)]
    public string $modality = '';

    #[LiveProp(writable: true)]
    public string $minContext = '';

    /**
     * @return list<ModelCatalogEntry>
     */
    public function getRows(): array
    {
        $mod = '' !== $this->modality ? ModelModality::tryFrom($this->modality) : null;

        $min = null;
        if ($this->minContext !== '' && ctype_digit($this->minContext)) {
            $min = (int) $this->minContext;
        }

        $criteria = new CatalogFilterCriteria(
            '' !== $this->query ? $this->query : null,
            '' !== $this->providerId ? $this->providerId : null,
            $mod instanceof ModelModality ? $mod : null,
            $min,
            null,
            [],
        );

        return $this->searchCatalogModels->handle($criteria);
    }

    public function getShowOver200kCostColumns(): bool
    {
        foreach ($this->getRows() as $entry) {
            if (null !== $entry->costPerMillionOver200kInput || null !== $entry->costPerMillionOver200kOutput) {
                return true;
            }
        }

        return false;
    }
}
