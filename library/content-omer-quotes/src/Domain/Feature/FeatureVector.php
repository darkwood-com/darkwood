<?php

declare(strict_types=1);

namespace App\Domain\Feature;

use App\Domain\Repository\RepositoryId;

final readonly class FeatureVector
{
    /**
     * @param array<string, float> $weights
     */
    public function __construct(
        public RepositoryId $repositoryId,
        public array $weights,
    ) {
    }
}
