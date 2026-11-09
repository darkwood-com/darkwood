<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\PipelineContext;
use App\TokenFlow\TokenEstimator;

final class StreamSinkStep implements PipelineStepInterface
{
    public function __construct(
        private readonly TokenEstimator $estimator = new TokenEstimator(),
    ) {
    }

    public static function name(): string
    {
        return 'sink';
    }

    public function withArgs(array $args): self
    {
        return $this;
    }

    public function apply(PipelineContext $context): PipelineContext
    {
        if ($context->chunks !== []) {
            $context->stream = implode("\n\n", $context->chunks);
        }

        $context->compressedCharCount = mb_strlen($context->stream);
        $context->compressedTokenEstimate = $this->estimator->estimate($context->stream);
        $context->recordStep($this->displayName());
        $context->addDebug(sprintf(
            'sink flushed %d chars (~%d estimated tokens)',
            $context->compressedCharCount,
            $context->compressedTokenEstimate,
        ));

        return $context;
    }

    public function displayName(): string
    {
        return 'sink';
    }
}
