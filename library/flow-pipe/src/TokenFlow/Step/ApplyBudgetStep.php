<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\PipelineContext;
use App\TokenFlow\TokenEstimator;

final class ApplyBudgetStep implements PipelineStepInterface
{
    private readonly TokenEstimator $estimator;

    public function __construct(
        private readonly int $limit = 1000,
        ?TokenEstimator $estimator = null,
    ) {
        $this->estimator = $estimator ?? new TokenEstimator();
    }

    public static function name(): string
    {
        return 'budget';
    }

    public function withArgs(array $args): self
    {
        return new self(
            isset($args['limit']) ? (int) $args['limit'] : $this->limit,
            $this->estimator,
        );
    }

    public function apply(PipelineContext $context): PipelineContext
    {
        $context->budget = $this->limit;

        if ($context->chunks === []) {
            $context->chunks = [$context->stream];
        }

        $kept = [];
        $accumulated = 0;

        foreach ($context->chunks as $chunk) {
            $estimate = $this->estimator->estimate($chunk);
            if ($accumulated + $estimate > $this->limit) {
                $remaining = $this->limit - $accumulated;
                if ($remaining > 0) {
                    $trimmed = $this->trimToTokenBudget($chunk, $remaining);
                    if ($trimmed !== '') {
                        $kept[] = $trimmed;
                        $accumulated += $this->estimator->estimate($trimmed);
                    }
                }
                break;
            }

            $kept[] = $chunk;
            $accumulated += $estimate;
        }

        $context->chunks = $kept;
        $context->recordStep($this->displayName());
        $context->addDebug(sprintf('budget:%d kept %d chunks (~%d estimated tokens)', $this->limit, count($kept), $accumulated));

        return $context;
    }

    public function displayName(): string
    {
        return sprintf('budget:%d', $this->limit);
    }

    private function trimToTokenBudget(string $text, int $tokenBudget): string
    {
        $maxChars = $tokenBudget * 4;
        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        return mb_substr($text, 0, $maxChars).'…';
    }
}
