<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Level state snapshot helpers (depth + seed ownership).
 */
final class Level
{
    public function __construct(
        public int $depth,
        public int $seed,
        public Dungeon $dungeon,
    ) {
    }

    public function isTown(): bool
    {
        return $this->depth === 0;
    }
}
