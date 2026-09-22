<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\ModelCatalog;

use Darkwood\AI\Platform\Exception\ModelNotFoundException;
use Darkwood\AI\Platform\Model;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
class ModelCatalog implements ModelCatalogInterface
{
    /**
     * @param array<string, Model> $models
     */
    public function __construct(
        private readonly array $models = [],
    ) {
    }

    public function getModel(string $modelName): Model
    {
        if (!isset($this->models[$modelName])) {
            throw new ModelNotFoundException(\sprintf('Model "%s" was not found in the catalog.', $modelName));
        }

        return $this->models[$modelName];
    }

    public function getModels(): array
    {
        return $this->models;
    }
}
