<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\ModelCatalog;

use Darkwood\AI\Platform\Exception\ModelNotFoundException;
use Darkwood\AI\Platform\Model;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
interface ModelCatalogInterface
{
    /**
     * @throws ModelNotFoundException
     */
    public function getModel(string $modelName): Model;

    /**
     * @return array<string, Model>
     */
    public function getModels(): array;
}
