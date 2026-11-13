<?php

declare(strict_types=1);

namespace App\Domain\Repository;

final readonly class RepositoryMetadata
{
    /**
     * @param list<string> $topics
     */
    public function __construct(
        public RepositoryId $id,
        public string $name,
        public string $fullName,
        public string $ownerLogin,
        public string $description,
        public string $cloneUrl,
        public string $defaultBranch,
        public array $topics = [],
    ) {
    }
}
