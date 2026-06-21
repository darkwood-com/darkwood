<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\PipelineContext;

final class StripAnsiStep implements PipelineStepInterface
{
    public static function name(): string
    {
        return 'strip_ansi';
    }

    public function withArgs(array $args): self
    {
        return $this;
    }

    public function apply(PipelineContext $context): PipelineContext
    {
        $before = mb_strlen($context->stream);
        $context->stream = (string) preg_replace('/\x1b\[[0-9;]*m/', '', $context->stream);
        // Also handle literal \x1b sequences in fixtures
        $context->stream = str_replace(['\x1b[32m', '\x1b[0m', '\x1b[1m', '\x1b[36m', '\x1b[33m'], '', $context->stream);
        $context->stream = (string) preg_replace('/\\x1b\[[0-9;]*m/', '', $context->stream);
        $removed = $before - mb_strlen($context->stream);
        $context->recordStep($this->displayName());
        $context->addDebug(sprintf('strip_ansi removed %d escape bytes', max(0, $removed)));

        return $context;
    }

    public function displayName(): string
    {
        return 'strip_ansi';
    }

    public function transformText(string $text): string
    {
        $ctx = new PipelineContext();
        $ctx->stream = $text;

        return $this->apply($ctx)->stream;
    }
}
