<?php

declare(strict_types=1);

namespace App\Flow\Instrumentation;

/**
 * Outcome-cost accounting for the thanks-agent demo.
 * Candidate for a future promotion into darkwood/flow.
 */
final class CostLedger
{
    public float $ratePer1kTokens = 0.002;

    /** @var array<string, array{tokens: int, cost: float, successes: int, errors: int}> */
    private array $byLabel = [];

    private int $push = 0;

    private int $pull = 0;

    private int $pop = 0;

    private int $async = 0;

    private int $complete = 0;

    private int $error = 0;

    private float $schedulingCost = 0.0;

    public function reset(): void
    {
        $this->byLabel = [];
        $this->push = 0;
        $this->pull = 0;
        $this->pop = 0;
        $this->async = 0;
        $this->complete = 0;
        $this->error = 0;
        $this->schedulingCost = 0.0;
    }

    public function recordPush(): void
    {
        ++$this->push;
        $this->schedulingCost += 0.0001;
    }

    public function recordPull(): void
    {
        ++$this->pull;
        $this->schedulingCost += 0.00005;
    }

    public function recordPop(): void
    {
        ++$this->pop;
        $this->schedulingCost += 0.00005;
    }

    public function recordAsync(): void
    {
        ++$this->async;
        $this->schedulingCost += 0.0002;
    }

    public function recordComplete(string $label, int $tokens = 0, float $extraCost = 0.0): void
    {
        ++$this->complete;
        $bucket = $this->bucket($label);
        ++$bucket['successes'];
        $bucket['tokens'] += $tokens;
        $bucket['cost'] += $this->tokenCost($tokens) + $extraCost;
        $this->byLabel[$label] = $bucket;
    }

    public function recordError(string $label, int $tokens = 0, float $extraCost = 0.0): void
    {
        ++$this->error;
        $bucket = $this->bucket($label);
        ++$bucket['errors'];
        $bucket['tokens'] += $tokens;
        $bucket['cost'] += $this->tokenCost($tokens) + $extraCost;
        $this->byLabel[$label] = $bucket;
    }

    public function tokenCost(int $tokens): float
    {
        return ($tokens / 1000) * $this->ratePer1kTokens;
    }

    /**
     * @return array{tokens: int, cost: float, successes: int, errors: int}
     */
    private function bucket(string $label): array
    {
        return $this->byLabel[$label] ?? [
            'tokens' => 0,
            'cost' => 0.0,
            'successes' => 0,
            'errors' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $agentCost = 0.0;
        $tokens = 0;
        $successes = 0;
        $errors = 0;
        foreach ($this->byLabel as $bucket) {
            $agentCost += $bucket['cost'];
            $tokens += $bucket['tokens'];
            $successes += $bucket['successes'];
            $errors += $bucket['errors'];
        }
        $total = $agentCost + $this->schedulingCost;

        return [
            'byLabel' => $this->byLabel,
            'events' => [
                'push' => $this->push,
                'pull' => $this->pull,
                'pop' => $this->pop,
                'async' => $this->async,
                'complete' => $this->complete,
                'error' => $this->error,
            ],
            'totals' => [
                'jobCost' => round($agentCost, 6),
                'schedulingCost' => round($this->schedulingCost, 6),
                'totalCost' => round($total, 6),
                'tokens' => $tokens,
                'successes' => $successes,
                'errors' => $errors,
                'costPerSuccessfulOutcome' => $successes > 0 ? round($total / $successes, 6) : null,
            ],
        ];
    }
}
