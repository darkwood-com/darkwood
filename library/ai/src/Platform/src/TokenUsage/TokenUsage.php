<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\TokenUsage;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class TokenUsage implements TokenUsageInterface
{
    public function __construct(
        private readonly ?int $promptTokens = null,
        private readonly ?int $completionTokens = null,
        private readonly ?string $model = null,
    ) {
    }

    public function getPromptTokens(): ?int
    {
        return $this->promptTokens;
    }

    public function getCompletionTokens(): ?int
    {
        return $this->completionTokens;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }
}
