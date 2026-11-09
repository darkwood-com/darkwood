<?php

declare(strict_types=1);

namespace App\Infrastructure\Flow\Model;

final class ChunkTask
{
    public function __construct(
        public readonly int $index,
        public readonly string $content,
    ) {
    }
}
