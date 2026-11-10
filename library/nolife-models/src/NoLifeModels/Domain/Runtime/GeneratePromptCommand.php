<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Runtime;

/**
 * Non-streaming text generation request (Ollama /api/generate shape).
 */
final readonly class GeneratePromptCommand
{
    public function __construct(
        public string $model,
        public string $prompt,
        public ?string $system,
        public float $temperature = 0.7,
        public float $topP = 0.9,
    ) {
    }
}
