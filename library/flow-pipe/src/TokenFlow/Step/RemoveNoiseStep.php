<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\PipelineContext;

final class RemoveNoiseStep implements PipelineStepInterface
{
    public static function name(): string
    {
        return 'remove_noise';
    }

    public function withArgs(array $args): self
    {
        return $this;
    }

    public function apply(PipelineContext $context): PipelineContext
    {
        $lines = preg_split('/\R/u', $context->stream) ?: [];
        $filtered = [];
        $previous = null;
        $dropped = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $normalized = (string) preg_replace(
                '/^\[\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?\]\s*/',
                '',
                $trimmed,
            );

            if ($this->isNoiseLine($normalized)) {
                ++$dropped;
                continue;
            }

            if ($normalized === $previous) {
                ++$dropped;
                continue;
            }

            $filtered[] = $normalized;
            $previous = $normalized;
        }

        $context->stream = implode("\n", $filtered);
        $context->recordStep($this->displayName());
        $context->addDebug(sprintf('remove_noise dropped %d lines', $dropped));

        return $context;
    }

    public function displayName(): string
    {
        return 'remove_noise';
    }

    private function isNoiseLine(string $line): bool
    {
        if ($line === '') {
            return false;
        }

        if (preg_match('/\[(DEBUG|TRACE)\]/', $line)) {
            return true;
        }

        if (preg_match('/^\s+at\s+\S+\(/', $line)) {
            return true;
        }

        if (preg_match('/^Metadata:\s*\{/', $line)) {
            return true;
        }

        if (str_starts_with($line, 'Disclaimer:')) {
            return true;
        }

        if (str_starts_with($line, 'Copyright (c)')) {
            return true;
        }

        if (str_starts_with($line, '[DEBUG] redundant footer')) {
            return true;
        }

        return false;
    }

    public function transformText(string $text): string
    {
        $ctx = new PipelineContext();
        $ctx->stream = $text;

        return $this->apply($ctx)->stream;
    }
}
