<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * Loaded BSP map bytes + validated header.
 * Lump payload decoding is EXPECTED_NOT_IMPLEMENTED in this iteration.
 */
final class BspMap
{
    public function __construct(
        public readonly BspHeader $header,
        public readonly string $bytes,
        public readonly string $sourceLabel,
    ) {
    }

    public function getFileSize(): int
    {
        return strlen($this->bytes);
    }

    public function getLump(int $index): BspLump
    {
        return $this->header->lumps[$index];
    }

    /**
     * Raw lump slice (for future readers). Empty if filelen == 0.
     */
    public function readLumpBytes(int $index): string
    {
        $lump = $this->getLump($index);
        if ($lump->filelen === 0) {
            return '';
        }

        return substr($this->bytes, $lump->fileofs, $lump->filelen);
    }
}
