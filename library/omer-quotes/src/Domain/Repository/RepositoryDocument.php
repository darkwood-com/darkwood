<?php

declare(strict_types=1);

namespace App\Domain\Repository;

final readonly class RepositoryDocument
{
    /**
     * @param list<string> $topics
     * @param list<string> $textFragments
     */
    public function __construct(
        public RepositoryId $id,
        public string $name,
        public string $fullName,
        public string $ownerLogin,
        public string $description,
        public array $topics,
        public array $textFragments,
    ) {
    }

    public function toCanonicalText(): string
    {
        return trim(implode("\n", array_filter([
            $this->name,
            $this->fullName,
            $this->ownerLogin,
            $this->description,
            implode(' ', $this->topics),
            ...$this->textFragments,
        ])));
    }
}
