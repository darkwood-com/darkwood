<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Benchmark;

final readonly class BenchmarkCase
{
    public function __construct(
        public string $id,
        public string $title,
        public string $prompt,
        public ?string $system,
        public string $locale,
    ) {
    }
}
