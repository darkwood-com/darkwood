<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darkwood\AI\Platform\Bridge\Jev;

use Darkwood\AI\Platform\Result\RawResultInterface;
use Darkwood\AI\Platform\TokenUsage\TokenUsage;
use Darkwood\AI\Platform\TokenUsage\TokenUsageExtractorInterface;
use Darkwood\AI\Platform\TokenUsage\TokenUsageInterface;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class TokenUsageExtractor implements TokenUsageExtractorInterface
{
    public function extract(RawResultInterface $rawResult, array $options = []): ?TokenUsageInterface
    {
        $content = $rawResult->getData();

        if (!isset($content['usage']) || !\is_array($content['usage'])) {
            return null;
        }

        $usage = $content['usage'];
        $model = $content['model'] ?? null;

        return new TokenUsage(
            promptTokens: isset($usage['input_tokens']) ? (int) $usage['input_tokens'] : null,
            completionTokens: isset($usage['output_tokens']) ? (int) $usage['output_tokens'] : null,
            model: \is_string($model) && '' !== $model ? $model : null,
        );
    }
}
