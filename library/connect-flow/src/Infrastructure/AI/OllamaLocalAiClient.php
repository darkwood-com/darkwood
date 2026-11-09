<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\Port\LocalAiClientInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;

/**
 * Thin wrapper around the Symfony AI PlatformInterface wired to the local Ollama instance.
 * All inference stays on-device — no data leaves the machine.
 */
final readonly class OllamaLocalAiClient implements LocalAiClientInterface
{
    public function __construct(
        private PlatformInterface $platform,
        private string $model,
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
