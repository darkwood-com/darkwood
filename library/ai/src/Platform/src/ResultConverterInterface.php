<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform;

use Darkwood\AI\Platform\Result\RawResultInterface;
use Darkwood\AI\Platform\Result\ResultInterface;
use Darkwood\AI\Platform\TokenUsage\TokenUsageExtractorInterface;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
interface ResultConverterInterface
{
    public function supports(Model $model): bool;

    /**
     * @param array<string, mixed> $options
     */
    public function convert(RawResultInterface $result, array $options = []): ResultInterface;

    public function getTokenUsageExtractor(): ?TokenUsageExtractorInterface;
}
