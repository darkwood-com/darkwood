<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Benchmark;

/**
 * Full matrix: cases × models.
 *
 * @param list<BenchmarkCellResult> $cells
 */
final readonly class BenchmarkRunReport
{
    public function __construct(
        public string $id,
        public \DateTimeImmutable $startedAt,
        public \DateTimeImmutable $finishedAt,
        public array $cells,
    ) {
    }
}
