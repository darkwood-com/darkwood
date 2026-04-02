<?php

namespace App\Domain\Port;

/**
 * Port: strategy for building prompts sent to the LLM.
 * Swap implementations to change the generation domain (e.g. SketchUp, Blender, OpenSCAD).
 */
interface PromptBuilderInterface
{
    public function buildSystemPrompt(): string;

    public function buildUserPrompt(string $designSpec): string;
}
