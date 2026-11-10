<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * lump_t — fileofs + filelen (qfiles.h).
 */
final class BspLump
{
    public function __construct(
        public readonly int $index,
        public readonly string $name,
        public readonly int $fileofs,
        public readonly int $filelen,
    ) {
    }
}
