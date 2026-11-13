<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface LanguageToLambdaPromptBuilderInterface
{
    public function systemPrompt(): string;

    public function userPrompt(string $userIntent): string;
}
