<?php

declare(strict_types=1);

namespace Quake3\Render;

/**
 * Decoded RGBA8 image — row order matches Q3 LoadTGA/LoadJPG upload buffers.
 */
final class DecodedImage
{
    public function __construct(
        public readonly int $width,
        public readonly int $height,
        public readonly string $rgba,
        public readonly bool $hasAlpha,
        public readonly string $format,
        public readonly string $sourcePath,
    ) {
        if (strlen($rgba) !== $width * $height * 4) {
            throw new \InvalidArgumentException('RGBA buffer size mismatch');
        }
    }

    public function checksum(): string
    {
        return hash('crc32b', $this->rgba);
    }
}
