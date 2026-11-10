<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Benchmark;

use App\NoLifeModels\Domain\Model\InferenceMetrics;

/**
 * One benchmark case executed against one local model tag.
 */
final readonly class BenchmarkCellResult
{
    public function __construct(
        public string $caseId,
        public string $modelName,
        public string $responseText,
        public ?string $error,
        public InferenceMetrics $metrics,
    ) {
    }
}
