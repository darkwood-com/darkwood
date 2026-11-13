<?php

declare(strict_types=1);

namespace App\Domain\Capability;

use App\Domain\Lambda\LambdaExpression;
use App\Domain\Port\LanguageToLambdaPromptBuilderInterface;
use App\Domain\Port\LocalAiClientInterface;

/**
 * Overlay: natural language → lambda (never bypasses the lambda execution path).
 */
final readonly class LanguageCapability
{
    public function __construct(
        private LocalAiClientInterface $ai,
        private LanguageToLambdaPromptBuilderInterface $prompts,
    ) {
    }

    public function textToLambda(string $userIntent): LambdaExpression
    {
        $system = $this->prompts->systemPrompt();
        $user = $this->prompts->userPrompt($userIntent);
        $raw = trim($this->ai->generate($system, $user));

        return new LambdaExpression($this->extractLambdaExpression($raw));
    }

    /**
     * Accepts raw model output; returns a single lambda expression line.
     */
    private function extractLambdaExpression(string $raw): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '' || str_starts_with($t, '#')) {
                continue;
            }

            return $t;
        }

        return $raw;
    }
}
