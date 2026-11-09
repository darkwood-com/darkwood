<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\PipelineContext;
use App\TokenFlow\TokenEstimator;

final class EstimateTokensStep implements PipelineStepInterface
{
    public function __construct(
        private readonly TokenEstimator $estimator = new TokenEstimator(),
    ) {
    }

    public static function name(): string
    {
        return 'estimate_tokens';
    }

    public function withArgs(array $args): self
    {
        return $this;
    }

    public function apply(PipelineContext $context): PipelineContext
    {
        $estimate = $this->estimator->estimate($context->stream);
        $context->recordStep($this->displayName());
        $context->addDebug(sprintf('estimate_tokens ~%d for current stream', $estimate));

        return $context;
    }

    public function displayName(): string
    {
        return 'estimate_tokens';
    }
}
