<?php

declare(strict_types=1);

namespace App\Domain\Clustering;

use App\Domain\Repository\RepositoryId;

final readonly class ClusterAssignment
{
    public function __construct(
        public RepositoryId $repositoryId,
        public int $clusterId,
    ) {
    }
}
