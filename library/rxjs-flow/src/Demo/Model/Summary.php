<?php

declare(strict_types=1);

namespace App\Demo\Model;

final readonly class Summary
{
    /**
     * @param list<ImageResult> $results
     */
    public function __construct(
        public array $results,
        public int $ok,
        public int $failed,
        public float $elapsedMs,
        public int $concurrency,
    ) {
    }

    /**
     * @return array{
     *     ok: int,
     *     failed: int,
     *     elapsed_ms: float,
     *     concurrency: int,
     *     results: list<array{url: string, path: ?string, checksum: ?string, success: bool, error: ?string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'failed' => $this->failed,
            'elapsed_ms' => round($this->elapsedMs, 2),
            'concurrency' => $this->concurrency,
            'results' => array_map(
                static fn (ImageResult $result): array => $result->toArray(),
                $this->results,
            ),
        ];
    }
}
