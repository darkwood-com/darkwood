<?php

declare(strict_types=1);

namespace App\Domain\Classification;

use App\Domain\Repository\RepositoryId;

final readonly class ClassificationResult
{
    /**
     * @param array<string, float> $scores
     */
    public function __construct(
        public RepositoryId $repositoryId,
        public string $label,
        public array $scores = [],
    ) {
    }
}
