<?php

declare(strict_types=1);

namespace App\Domain\Port;

/**
 * Port: DESIGN AGENT prompts.
 * Takes a raw human intention and instructs the LLM to produce a JSON design specification.
 */
interface DesignPromptBuilderInterface
{
    public function buildSystemPrompt(): string;

    public function buildUserPrompt(string $intention): string;
}
