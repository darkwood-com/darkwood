<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\TokenUsage;

use Darkwood\AI\Platform\Result\RawResultInterface;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
interface TokenUsageExtractorInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function extract(RawResultInterface $rawResult, array $options = []): ?TokenUsageInterface;
}
