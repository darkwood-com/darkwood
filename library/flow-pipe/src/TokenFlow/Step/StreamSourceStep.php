<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\Fixture\FlowFixtureProvider;
use App\TokenFlow\PipelineContext;
use App\TokenFlow\TokenEstimator;

final class StreamSourceStep implements PipelineStepInterface
{
    public function __construct(
        private readonly FlowFixtureProvider $fixtures,
        private readonly TokenEstimator $estimator,
    ) {
    }

    public static function name(): string
    {
        return 'source';
    }

    public function withArgs(array $args): self
    {
        return $this;
    }

    public function apply(PipelineContext $context): PipelineContext
    {
        $key = $context->inputKey !== '' ? $context->inputKey : 'flow-engine-log';
        $context->stream = $this->fixtures->get($key);
        $context->originalCharCount = mb_strlen($context->stream);
        $context->originalTokenEstimate = $this->estimator->estimate($context->stream);
        $context->recordStep($this->displayName());
        $context->addDebug(sprintf('source loaded fixture=%s chars=%d', $key, $context->originalCharCount));

        return $context;
    }

    public function displayName(): string
    {
        return 'source';
    }
}
