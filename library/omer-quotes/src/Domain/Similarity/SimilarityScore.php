<?php

declare(strict_types=1);

namespace App\Domain\Similarity;

use App\Domain\Repository\RepositoryId;

final readonly class SimilarityScore
{
    public function __construct(
        public RepositoryId $source,
        public RepositoryId $target,
        public float $score,
    ) {
    }
}
