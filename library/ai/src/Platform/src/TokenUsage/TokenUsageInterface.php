<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\TokenUsage;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
interface TokenUsageInterface
{
    public function getPromptTokens(): ?int;

    public function getCompletionTokens(): ?int;

    public function getModel(): ?string;
}
