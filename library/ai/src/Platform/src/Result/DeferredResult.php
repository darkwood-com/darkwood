<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\Result;

use Darkwood\AI\Platform\Exception\RuntimeException;
use Darkwood\AI\Platform\ResultConverterInterface;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class DeferredResult
{
    private ?ResultInterface $converted = null;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly ResultConverterInterface $converter,
        private readonly RawResultInterface $rawResult,
        private readonly array $options = [],
    ) {
    }

    public function getResult(): ResultInterface
    {
        if (null === $this->converted) {
            $this->converted = $this->converter->convert($this->rawResult, $this->options);

            if (null === $this->converted->getRawResult()) {
                $this->converted->setRawResult($this->rawResult);
            }

            $extractor = $this->converter->getTokenUsageExtractor();
            if (null !== $extractor) {
                $usage = $extractor->extract($this->rawResult, $this->options);
                if (null !== $usage) {
                    $this->converted->getMetadata()->set('token_usage', $usage);
                }
            }
        }

        return $this->converted;
    }

    /**
     * @return object|array<string, mixed>
     */
    public function asObject(): object|array
    {
        $result = $this->getResult();

        if (!$result instanceof ObjectResult) {
            throw new RuntimeException('The converted result is not an object result.');
        }

        return $result->getContent();
    }
}
