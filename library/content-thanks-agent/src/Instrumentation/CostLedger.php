<?php

declare(strict_types=1);

namespace App\Instrumentation;

use App\Model\AgentId;
use App\Model\AgentResult;

/**
 * Demo ledger for agent scoreboards.
 *
 * Companion outcome ledger: {@see \App\Flow\Instrumentation\CostLedger}.
 */
final class CostLedger
{
    public const RATE_PER_1K_TOKENS = 0.002;

    /** @var array<string, array{messages: int, tokens: int, cost: float, latencyMs: float, value: int, errors: int, successes: int}> */
    private array $byAgent = [];

    private float $schedulingCost = 0.0;

    private int $pushCount = 0;

    private int $pullCount = 0;

    private int $popCount = 0;

    private int $asyncCount = 0;

    public function reset(): void
    {
        $this->byAgent = [];
        $this->schedulingCost = 0.0;
        $this->pushCount = 0;
        $this->pullCount = 0;
        $this->popCount = 0;
        $this->asyncCount = 0;
    }

    public function recordPush(): void
    {
        ++$this->pushCount;
        $this->schedulingCost += 0.0001;
    }

    public function recordPull(): void
    {
        ++$this->pullCount;
        $this->schedulingCost += 0.00005;
    }

    public function recordPop(): void
    {
        ++$this->popCount;
        $this->schedulingCost += 0.00005;
    }

    public function recordAsync(): void
    {
        ++$this->asyncCount;
        $this->schedulingCost += 0.0002;
    }

    public function recordResult(AgentResult $result): void
    {
        $key = $result->agentId->value;
        $bucket = $this->byAgent[$key] ?? [
            'messages' => 0,
            'tokens' => 0,
            'cost' => 0.0,
            'latencyMs' => 0.0,
            'value' => 0,
            'errors' => 0,
            'successes' => 0,
        ];

        $bucket['messages'] += $result->messages;
        $bucket['tokens'] += $result->tokens;
        $bucket['cost'] += $result->cost;
        $bucket['latencyMs'] += $result->latencyMs;
        $bucket['value'] += $result->value;

        if ($result->success) {
            ++$bucket['successes'];
        } else {
            ++$bucket['errors'];
            // Failure path is still billed (PR #2363).
            $bucket['cost'] += self::tokenCost(max(1, $result->tokens));
        }

        $this->byAgent[$key] = $bucket;
    }

    public function recordTerminalError(AgentId $agentId, int $tokens, float $cost): void
    {
        $key = $agentId->value;
        $bucket = $this->byAgent[$key] ?? [
            'messages' => 0,
            'tokens' => 0,
            'cost' => 0.0,
            'latencyMs' => 0.0,
            'value' => 0,
            'errors' => 0,
            'successes' => 0,
        ];
        ++$bucket['errors'];
        $bucket['tokens'] += $tokens;
        $bucket['cost'] += $cost;
        $this->byAgent[$key] = $bucket;
    }

    public static function tokenCost(int $tokens): float
    {
        return ($tokens / 1000) * self::RATE_PER_1K_TOKENS;
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $agentCost = 0.0;
        $agentTokens = 0;
        $agentMessages = 0;
        $successes = 0;
        $errors = 0;

        foreach ($this->byAgent as $bucket) {
            $agentCost += $bucket['cost'];
            $agentTokens += $bucket['tokens'];
            $agentMessages += $bucket['messages'];
            $successes += $bucket['successes'];
            $errors += $bucket['errors'];
        }

        $totalCost = $agentCost + $this->schedulingCost;

        return [
            'byAgent' => $this->byAgent,
            'scheduling' => [
                'push' => $this->pushCount,
                'pull' => $this->pullCount,
                'pop' => $this->popCount,
                'async' => $this->asyncCount,
                'cost' => round($this->schedulingCost, 6),
            ],
            'totals' => [
                'agentCost' => round($agentCost, 6),
                'schedulingCost' => round($this->schedulingCost, 6),
                'totalCost' => round($totalCost, 6),
                'tokens' => $agentTokens,
                'messages' => $agentMessages,
                'successes' => $successes,
                'errors' => $errors,
                'costPerSuccessfulOutcome' => $successes > 0 ? round($totalCost / $successes, 6) : null,
            ],
        ];
    }
}
