<?php

declare(strict_types=1);

namespace App\Model;

final readonly class RunReport
{
    /**
     * @param list<AgentResult>    $results
     * @param array<string, mixed> $flowMetrics
     */
    public function __construct(
        public string $runId,
        public bool $withThanks,
        public string $driver,
        public float $wallTimeMs,
        public array $results,
        public array $flowMetrics,
        public int $totalValue,
        public float $totalCost,
        public int $totalMessages,
        public int $totalTokens,
        public int $usefulWorkCount,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'runId' => $this->runId,
            'withThanks' => $this->withThanks,
            'driver' => $this->driver,
            'wallTimeMs' => round($this->wallTimeMs, 2),
            'totalValue' => $this->totalValue,
            'totalCost' => round($this->totalCost, 6),
            'totalMessages' => $this->totalMessages,
            'totalTokens' => $this->totalTokens,
            'usefulWorkCount' => $this->usefulWorkCount,
            'costPerValue' => $this->totalValue > 0 ? round($this->totalCost / $this->totalValue, 6) : null,
            'flowMetrics' => $this->flowMetrics,
            'results' => array_map(static fn (AgentResult $r) => $r->toArray(), $this->results),
            'verdict' => $this->withThanks
                ? 'The sixth agent adds no value; it still consumes scheduling, context, logging, and observability.'
                : 'Five useful agents produced measurable value.',
        ];
    }
}
