<?php

declare(strict_types=1);

namespace App\Infrastructure\Flow\Model;

final class ChunkMergeCollector
{
    /** @var array<int, string> */
    private array $chunks = [];

    public function store(int $index, string $content): void
    {
        $this->chunks[$index] = $content;
    }

    /**
     * @return list<string>
     */
    public function ordered(): array
    {
        ksort($this->chunks);

        return array_values($this->chunks);
    }
}
