<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform;

use Darkwood\AI\Platform\Exception\InvalidArgumentException;
use Darkwood\AI\Platform\ModelCatalog\ModelCatalogInterface;
use Darkwood\AI\Platform\Result\DeferredResult;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class Platform implements PlatformInterface
{
    public function __construct(
        private readonly ModelCatalogInterface $modelCatalog,
        private readonly ModelClientInterface $modelClient,
        private readonly ResultConverterInterface $resultConverter,
    ) {
    }

    public function invoke(string $model, array|string $input, array $options = []): DeferredResult
    {
        $resolved = $this->modelCatalog->getModel($model);

        if (!$this->modelClient->supports($resolved)) {
            throw new InvalidArgumentException(\sprintf('The configured model client does not support "%s".', $model));
        }

        if (!$this->resultConverter->supports($resolved)) {
            throw new InvalidArgumentException(\sprintf('The configured result converter does not support "%s".', $model));
        }

        $rawResult = $this->modelClient->request($resolved, $input, $options);

        return new DeferredResult($this->resultConverter, $rawResult, $options);
    }

    public function getModelCatalog(): ModelCatalogInterface
    {
        return $this->modelCatalog;
    }
}
