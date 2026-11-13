<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\PipelineContext;

final class NormalizeWhitespaceStep implements PipelineStepInterface
{
    public static function name(): string
    {
        return 'normalize_whitespace';
    }

    public function withArgs(array $args): self
    {
        return $this;
    }

    public function apply(PipelineContext $context): PipelineContext
    {
        $context->stream = (string) preg_replace('/[ \t]+/u', ' ', $context->stream);
        $context->stream = (string) preg_replace('/\n{3,}/u', "\n\n", $context->stream);
        $context->stream = trim($context->stream);
        $context->recordStep($this->displayName());
        $context->addDebug('normalize_whitespace collapsed horizontal and blank runs');

        return $context;
    }

    public function displayName(): string
    {
        return 'normalize_whitespace';
    }

    public function transformText(string $text): string
    {
        $ctx = new PipelineContext();
        $ctx->stream = $text;

        return $this->apply($ctx)->stream;
    }
}
