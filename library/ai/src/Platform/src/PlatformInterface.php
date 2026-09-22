<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform;

use Darkwood\AI\Platform\ModelCatalog\ModelCatalogInterface;
use Darkwood\AI\Platform\Result\DeferredResult;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
interface PlatformInterface
{
    /**
     * @param array<string, mixed>|string $input
     * @param array<string, mixed>        $options
     */
    public function invoke(string $model, array|string $input, array $options = []): DeferredResult;

    public function getModelCatalog(): ModelCatalogInterface;
}
