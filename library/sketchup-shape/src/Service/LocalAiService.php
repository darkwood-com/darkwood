<?php

namespace App\Service;

use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;

/**
 * Thin wrapper around the Symfony AI PlatformInterface wired to the local Ollama instance.
 * All inference stays on-device — no data leaves the machine (RGPD compliant).
 */
final class LocalAiService
{
    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly string $model,
    ) {
    }

    public function generate(string $systemPrompt, string $userPrompt): string
    {
        $messages = new MessageBag(
            Message::forSystem($systemPrompt),
            Message::ofUser($userPrompt),
        );

        return $this->platform->invoke($this->model, $messages)->asText();
    }
}
