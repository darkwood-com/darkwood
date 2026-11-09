<?php

declare(strict_types=1);

namespace App\Domain\Port;

/**
 * Local inference only (e.g. Ollama). Implementations live in Infrastructure.
 */
interface LocalAiClientInterface
{
    public function generate(string $systemPrompt, string $userPrompt): string;
}
