<?php

declare(strict_types=1);

namespace App\Io;

final readonly class BenchResult
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public string $runner,
        public float $wallTimeMs,
        public float $busyTimeMs,
        public float $idleTimeMs,
        public int $tasksCompleted,
        public int $thanksIncluded,
        public float $totalCost,
        public int $totalValue,
        public int $totalMessages,
        public array $extra = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'runner' => $this->runner,
            'wallTimeMs' => round($this->wallTimeMs, 2),
            'busyTimeMs' => round($this->busyTimeMs, 2),
            'idleTimeMs' => round($this->idleTimeMs, 2),
            'tasksCompleted' => $this->tasksCompleted,
            'thanksIncluded' => $this->thanksIncluded,
            'totalCost' => round($this->totalCost, 6),
            'totalValue' => $this->totalValue,
            'totalMessages' => $this->totalMessages,
            'extra' => $this->extra,
        ];
    }
}
