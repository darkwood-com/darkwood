<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Benchmark;

interface BenchmarkRepositoryInterface
{
    /**
     * @return list<BenchmarkCase>
     */
    public function findAll(): array;

    public function find(string $id): ?BenchmarkCase;
}
